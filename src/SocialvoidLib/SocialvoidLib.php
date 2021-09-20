<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    /** @noinspection PhpUnused */
    /** @noinspection PhpRedundantDocCommentInspection */
    /** @noinspection PhpMissingFieldTypeInspection */

    // TODO: Add the ability to retrieve user likes & reposts

    namespace SocialvoidLib;

    use acm\acm;
    use acm\Objects\Schema;
    use BackgroundWorker\BackgroundWorker;
    use Exception;
    use mysqli;
    use Redis;
    use SocialvoidLib\Exceptions\GenericInternal\BackgroundWorkerNotEnabledException;
    use SocialvoidLib\Exceptions\GenericInternal\ConfigurationError;
    use SocialvoidLib\Exceptions\GenericInternal\DependencyError;
    use SocialvoidLib\Exceptions\GenericInternal\RedisCacheException;
    use SocialvoidLib\Managers\BasicRedisCacheManager;
    use SocialvoidLib\Managers\CoaAuthenticationManager;
    use SocialvoidLib\Managers\DocumentsManager;
    use SocialvoidLib\Managers\FollowerDataManager;
    use SocialvoidLib\Managers\FollowerStateManager;
    use SocialvoidLib\Managers\LikesRecordManager;
    use SocialvoidLib\Managers\PostsManager;
    use SocialvoidLib\Managers\QuotesRecordManager;
    use SocialvoidLib\Managers\ReplyRecordManager;
    use SocialvoidLib\Managers\RepostsRecordManager;
    use SocialvoidLib\Managers\ServiceJobManager;
    use SocialvoidLib\Managers\SessionManager;
    use SocialvoidLib\Managers\TelegramCdnManager;
    use SocialvoidLib\Managers\TimelineManager;
    use SocialvoidLib\Managers\UserManager;
    use udp2\udp2;

    /**
     * Class SocialvoidLib
     * @package SocialvoidLib
     */
    class SocialvoidLib
    {
        /**
         * @var acm
         */
        private acm $acm;

        /**
         * @var mixed
         */
        private $DatabaseConfiguration;

        /**
         * @var mixed
         */
        private $NetworkConfiguration;

        /**
         * @var mixed
         */
        private $DataStorageConfiguration;

        /**
         * @var udp2
         */
        private udp2 $UserDisplayPictureManager;

        /**
         * @var mysqli|null
         */
        private $database;

        /**
         * @var UserManager|null
         */
        private $UserManager;

        /**
         * @var FollowerStateManager|null
         */
        private $FollowerStateManager;

        /**
         * @var SessionManager|null
         */
        private $SessionManager;

        /**
         * @var FollowerDataManager|null
         */
        private $FollowerDataManager;

        /**
         * @var mixed
         */
        private $ServiceEngineConfiguration;

        /**
         * @var PostsManager|null
         */
        private $PostsManager;

        /**
         * @var BackgroundWorker|null
         */
        private $BackgroundWorker;

        /*
         * Indicates if background worker was initialized or not
         *
         * @var bool
         */
        private bool $BackgroundWorkerInitialized = false;

        /**
         * @var LikesRecordManager|null
         */
        private $LikesRecordManager;

        /**
         * @var RepostsRecordManager|null
         */
        private $RepostsRecordManager;

        /**
         * @var TimelineManager|null
         */
        private $TimelineManager;

        /**
         * @var QuotesRecordManager|null
         */
        private $QuotesRecordManager;

        /**
         * @var DocumentsManager|null
         */
        private $DocumentsManager;

        /**
         * @var CoaAuthenticationManager|null
         */
        private $CoaAuthenticationManager;

        /**
         * @var ServiceJobManager|null
         */
        private $ServiceJobManager;

        /**
         * @var Redis|null
         */
        private $BasicRedis;

        /**
         * @var mixed
         */
        private $EngineConfiguration;

        /**
         * @var mixed
         */
        private $RedisBasicCacheConfiguration;

        /**
         * @var BasicRedisCacheManager|null
         */
        private $BasicRedisCacheManager;

        /**
         * @var ReplyRecordManager
         */
        private $ReplyRecordManager;
        /**
         * @var mixed
         */
        private $CdnConfiguration;

        /**
         * @var TelegramCdnManager
         */
        private $TelegramCdnManager;

        /**
         * @var mixed
         */
        private $RpcServerConfiguration;

        /**
         * SocialvoidLib constructor.
         * @throws ConfigurationError
         * @throws DependencyError
         */
        public function __construct()
        {
            // Advanced Configuration Manager
            $this->acm = new acm(__DIR__, 'SocialvoidLib');

            // Database Schema Configuration
            $DatabaseSchema = new Schema();
            $DatabaseSchema->setDefinition("Host", "127.0.0.1");
            $DatabaseSchema->setDefinition("Port", "3306");
            $DatabaseSchema->setDefinition("Username", "root");
            $DatabaseSchema->setDefinition("Password", "");
            $DatabaseSchema->setDefinition("Name", 'socialvoid');
            $this->acm->defineSchema('Database', $DatabaseSchema);

            // Network Schema Configuration
            $NetworkSchema = new Schema();
            $NetworkSchema->setDefinition("Domain", "socialvoid.cc");
            $NetworkSchema->setDefinition("Name", "Socialvoid");
            $NetworkSchema->setDefinition("UnauthorizedSessionTTL", 600);
            $NetworkSchema->setDefinition("AuthorizedSessionTTL", 259200);
            $this->acm->defineSchema("Network", $NetworkSchema);

            // RPC Schema Configuration
            $RpcSchema = new Schema();
            $RpcSchema->setDefinition("EnableBackgroundWorker", True);
            $RpcSchema->setDefinition("Workers", 100);
            $RpcSchema->setDefinition("GearmanHost", "127.0.0.1");
            $RpcSchema->setDefinition("GearmanPort", 4730);
            $RpcSchema->setDefinition("ServerName", "Socialvoid RPC");
            $RpcSchema->setDefinition("MaxRequests", 20);
            $this->acm->defineSchema("RpcServer", $RpcSchema);

            // Service Engine Schema Configuration
            $ServiceEngineSchema = new Schema();
            $ServiceEngineSchema->setDefinition("EnableBackgroundWorker", True);
            $ServiceEngineSchema->setDefinition("GearmanHost", "127.0.0.1");
            $ServiceEngineSchema->setDefinition("GearmanPort", 4730);
            $ServiceEngineSchema->setDefinition("QueryWorkers", 30);
            $ServiceEngineSchema->setDefinition("UpdateWorkers", 20);
            $ServiceEngineSchema->setDefinition("HeavyWorkers", 5);
            $ServiceEngineSchema->setDefinition("DisplayOutput", true);
            $this->acm->defineSchema("ServiceEngine", $ServiceEngineSchema);

            // Engine Schema Configuration
            $EngineSchema = new Schema();
            $EngineSchema->setDefinition("MaxPeerResolveCacheCount", 20);
            $EngineSchema->setDefinition("TimelineMaxSize", 3200);
            $EngineSchema->setDefinition("TimelineChunkSize", 20);
            $this->acm->defineSchema("Engine", $EngineSchema);

            // CDN Schema Configuration
            $CdnSchema = new Schema();
            $CdnSchema->setDefinition("CdnEndpoint", "https://cdn.socialvoid.cc");
            $CdnSchema->setDefinition("MaxFileUploadSize", 26214400); // 25 MB
            $CdnSchema->setDefinition("TelegramCdnEnabled", True);
            $CdnSchema->setDefinition("TelegramCdnEnabled", True);
            $CdnSchema->setDefinition("TelegramBotToken", "<BOT TOKEN>");
            $CdnSchema->setDefinition("TelegramChannels", []);
            $this->acm->defineSchema("CDN", $CdnSchema);

            // Redis Basic Cache (Entity resolve cache)
            $RedisBasicCacheSchema = new Schema();
            $RedisBasicCacheSchema->setDefinition("Enabled", True);
            $RedisBasicCacheSchema->setDefinition("UseAuthentication", True);
            $RedisBasicCacheSchema->setDefinition("UseCompression", True);
            $RedisBasicCacheSchema->setDefinition("CompressionLevel", 9);
            $RedisBasicCacheSchema->setDefinition("PeerCacheEnabled", True);
            $RedisBasicCacheSchema->setDefinition("PeerCacheTTL", 500);
            $RedisBasicCacheSchema->setDefinition("PeerCacheLimit", 1000);
            $RedisBasicCacheSchema->setDefinition("PostCacheEnabled", True);
            $RedisBasicCacheSchema->setDefinition("PostCacheTTL", 300);
            $RedisBasicCacheSchema->setDefinition("PostCacheLimit", 1000);
            $RedisBasicCacheSchema->setDefinition("SessionCacheEnabled", True);
            $RedisBasicCacheSchema->setDefinition("SessionCacheTTL", 300);
            $RedisBasicCacheSchema->setDefinition("SessionCacheLimit", 1000);
            $RedisBasicCacheSchema->setDefinition("DocumentCacheEnabled", True);
            $RedisBasicCacheSchema->setDefinition("DocumentCacheTTL", 300);
            $RedisBasicCacheSchema->setDefinition("DocumentCacheLimit", 1000);
            $RedisBasicCacheSchema->setDefinition("TelegramCdnCacheEnabled", True);
            $RedisBasicCacheSchema->setDefinition("TelegramCdnCacheTTL", 300);
            $RedisBasicCacheSchema->setDefinition("TelegramCdnCacheLimit", 1000);
            $RedisBasicCacheSchema->setDefinition("RedisHost", "127.0.0.1");
            $RedisBasicCacheSchema->setDefinition("RedisPort", 6379);
            $RedisBasicCacheSchema->setDefinition("Password", "admin");
            $this->acm->defineSchema("RedisBasicCache", $RedisBasicCacheSchema);

            // Data storage Schema Configuration
            $DataStorageSchema = new Schema();
            $DataStorageSchema->setDefinition("UserAvatarsLocation", "/var/socialvoid/avatars");
            $DataStorageSchema->setDefinition("LegalDocumentsLocation", "/var/socialvoid/legal");
            $DataStorageSchema->setDefinition("WorkingLocation", "/var/socialvoid/lib");
            $this->acm->defineSchema("DataStorage", $DataStorageSchema);

            try
            {
                $this->DatabaseConfiguration = $this->acm->getConfiguration("Database");
                $this->NetworkConfiguration = $this->acm->getConfiguration("Network");
                $this->DataStorageConfiguration = $this->acm->getConfiguration("DataStorage");
                $this->ServiceEngineConfiguration = $this->acm->getConfiguration("ServiceEngine");
                $this->EngineConfiguration = $this->acm->getConfiguration("Engine");
                $this->RedisBasicCacheConfiguration = $this->acm->getConfiguration("RedisBasicCache");
                $this->CdnConfiguration = $this->acm->getConfiguration("CDN");
                $this->RpcServerConfiguration = $this->acm->getConfiguration("RpcServer");
            }
            catch(Exception $e)
            {
                throw new ConfigurationError("There was an error while trying to load ACM", 0, $e);
            }

            // Initialize constants
            self::defineLibConstant("SOCIALVOID_LIB_MAX_PEER_RESOLVE_CACHE_COUNT", $this->getEngineConfiguration()["MaxPeerResolveCacheCount"]);
            self::defineLibConstant("SOCIALVOID_LIB_TIMELINE_MAX_SIZE", $this->getEngineConfiguration()["TimelineMaxSize"]);
            self::defineLibConstant("SOCIALVOID_LIB_TIMELINE_CHUNK_SIZE", $this->getEngineConfiguration()["TimelineChunkSize"]);

            self::defineLibConstant("SOCIALVOID_LIB_BACKGROUND_WORKER_ENABLED", (bool)$this->getServiceEngineConfiguration()["EnableBackgroundWorker"]);
            self::defineLibConstant("SOCIALVOID_LIB_BACKGROUND_QUERY_WORKERS", (int)$this->getServiceEngineConfiguration()["QueryWorkers"]);
            self::defineLibConstant("SOCIALVOID_LIB_BACKGROUND_UPDATE_WORKERS", (int)$this->getServiceEngineConfiguration()["UpdateWorkers"]);
            self::defineLibConstant("SOCIALVOID_LIB_BACKGROUND_HEAVY_WORKERS", (int)$this->getServiceEngineConfiguration()["HeavyWorkers"]);

            self::defineLibConstant("SOCIALVOID_LIB_BASIC_CACHE_ENABLED", (bool)$this->getRedisBasicCacheConfiguration()["Enabled"]);

            self::defineLibConstant("SOCIALVOID_LIB_NETWORK_DOMAIN", $this->getNetworkConfiguration()["Domain"]);
            self::defineLibConstant("SOCIALVOID_LIB_NETWORK_NAME", $this->getNetworkConfiguration()["Name"]);

            // Initialize UDP
            try
            {
                $this->UserDisplayPictureManager = new udp2();
                $this->UserDisplayPictureManager->setStorageLocation($this->DataStorageConfiguration['UserAvatarsLocation']);
            }
            catch(Exception $e)
            {
                throw new DependencyError("There was an error while trying to initialize UDP", 0, $e);
            }

            if($this->getServiceEngineConfiguration()["EnableBackgroundWorker"] && function_exists("gearman_version") == false)
                throw new DependencyError("ServiceEngine has BackgroundWorker enabled but the gearman extension (php-gearman) is not installed.");
        }

        /**
         * Defines a library constant
         *
         * @param string $name
         * @param $value
         * @return void
         */
        private static function defineLibConstant(string $name, $value): void
        {
            if(defined($name))
                return;

            define($name, $value);
        }


        /**
         * @return acm
         */
        public function getAcm(): acm
        {
            return $this->acm;
        }

        /**
         * @return mysqli|null
         */
        public function getDatabase(): ?mysqli
        {
            if($this->database == null)
            {
                $this->connectDatabase();
            }

            return $this->database;
        }


        /**
         * Closes the current database connection
         */
        public function disconnectDatabase()
        {
            $this->database->close();
            $this->database = null;
        }

        /**
         * Creates a new database connection
         */
        public function connectDatabase()
        {
            if($this->database !== null)
            {
                $this->disconnectDatabase();
            }

            $this->database = new mysqli(
                $this->DatabaseConfiguration["Host"],
                $this->DatabaseConfiguration["Username"],
                $this->DatabaseConfiguration["Password"],
                $this->DatabaseConfiguration["Name"],
                $this->DatabaseConfiguration["Port"]
            );
        }

        /**
         * @return udp2
         */
        public function getUserDisplayPictureManager(): udp2
        {
            return $this->UserDisplayPictureManager;
        }

        /**
         * @return mixed
         */
        public function getDataStorageConfiguration()
        {
            return $this->DataStorageConfiguration;
        }

        /**
         * @return mixed
         */
        public function getNetworkConfiguration()
        {
            return $this->NetworkConfiguration;
        }

        /**
         * @return mixed
         */
        public function getDatabaseConfiguration()
        {
            return $this->DatabaseConfiguration;
        }

        /**
         * @return UserManager
         */
        public function getUserManager(): UserManager
        {
            if($this->UserManager == null)
                $this->UserManager = new UserManager($this);
            
            return $this->UserManager;
        }

        /**
         * @return FollowerStateManager
         */
        public function getFollowerStateManager(): FollowerStateManager
        {
            if($this->FollowerStateManager == null)
                $this->FollowerStateManager = new FollowerStateManager($this);
            return $this->FollowerStateManager;
        }

        /**
         * @return SessionManager
         */
        public function getSessionManager(): SessionManager
        {
            if($this->SessionManager == null)
                $this->SessionManager = new SessionManager($this);
            return $this->SessionManager;
        }

        /**
         * @return FollowerDataManager
         */
        public function getFollowerDataManager(): FollowerDataManager
        {
            if($this->FollowerDataManager == null)
                $this->FollowerDataManager = new FollowerDataManager($this);
            return $this->FollowerDataManager;
        }

        /**
         * @return mixed
         */
        public function getServiceEngineConfiguration()
        {
            return $this->ServiceEngineConfiguration;
        }

        /**
         * @return PostsManager
         */
        public function getPostsManager(): PostsManager
        {
            if($this->PostsManager == null)
                $this->PostsManager = new PostsManager($this);
            return $this->PostsManager;
        }

        /**
         * Returns background worker and initializes it if it's not already initialized
         *
         * @return BackgroundWorker
         * @throws BackgroundWorkerNotEnabledException
         */
        public function getBackgroundWorker(): BackgroundWorker
        {
            if((bool)$this->getServiceEngineConfiguration()["EnableBackgroundWorker"] == false)
                throw new BackgroundWorkerNotEnabledException("BackgroundWorker is not enabled for this build");

            if($this->BackgroundWorker == null)
                $this->BackgroundWorker = new BackgroundWorker();

            if($this->BackgroundWorkerInitialized == false)
            {
                $this->BackgroundWorker->getClient()->addServer(
                    $this->getServiceEngineConfiguration()["GearmanHost"],
                    (int)$this->getServiceEngineConfiguration()["GearmanPort"]
                );

                $this->BackgroundWorkerInitialized = true;
            }

            return $this->BackgroundWorker;
        }

        /**
         * @return LikesRecordManager
         */
        public function getLikesRecordManager(): LikesRecordManager
        {
            if($this->LikesRecordManager == null)
                $this->LikesRecordManager = new LikesRecordManager($this);
            return $this->LikesRecordManager;
        }

        /**
         * @return RepostsRecordManager
         */
        public function getRepostsRecordManager(): RepostsRecordManager
        {
            if($this->RepostsRecordManager == null)
                $this->RepostsRecordManager = new RepostsRecordManager($this);
            return $this->RepostsRecordManager;
        }

        /**
         * @return TimelineManager
         */
        public function getTimelineManager(): TimelineManager
        {
            if($this->TimelineManager == null)
                $this->TimelineManager = new TimelineManager($this);
            return $this->TimelineManager;
        }

        /**
         * @return QuotesRecordManager
         */
        public function getQuotesRecordManager(): QuotesRecordManager
        {
            if($this->QuotesRecordManager == null)
                $this->QuotesRecordManager = new QuotesRecordManager($this);
            return $this->QuotesRecordManager;
        }

        /**
         * @return ReplyRecordManager
         */
        public function getReplyRecordManager(): ReplyRecordManager
        {
            if($this->ReplyRecordManager == null)
                $this->ReplyRecordManager = new ReplyRecordManager($this);
            return $this->ReplyRecordManager;
        }

        /**
         * @return CoaAuthenticationManager
         */
        public function getCoaAuthenticationManager(): CoaAuthenticationManager
        {
            if($this->CoaAuthenticationManager == null)
                $this->CoaAuthenticationManager = new CoaAuthenticationManager($this);
            return $this->CoaAuthenticationManager;
        }

        /**
         * @return ServiceJobManager
         */
        public function getServiceJobManager(): ServiceJobManager
        {
            if($this->ServiceJobManager == null)
                $this->ServiceJobManager = new ServiceJobManager($this);
            return $this->ServiceJobManager;
        }

        /**
         * @return mixed
         */
        public function getEngineConfiguration()
        {
            return $this->EngineConfiguration;
        }

        /**
         * @return mixed
         */
        public function getRedisBasicCacheConfiguration()
        {
            return $this->RedisBasicCacheConfiguration;
        }

        /**
         * Connects to the Basic Redis cache server
         *
         * @throws DependencyError
         * @throws RedisCacheException
         */
        public function connectBasicRedis()
        {
            if($this->BasicRedis !== null && $this->BasicRedis->isConnected())
                return;

            if($this->getRedisBasicCacheConfiguration()["Enabled"] == false)
                throw new RedisCacheException("RedisBasicCache is not enabled");

            if(class_exists("Redis") == false)
                throw new DependencyError("RedisBasicCache is enabled but the Redis Extension (php-redis) is not installed");

            if($this->BasicRedis == null)
                $this->BasicRedis = new Redis();

            if($this->BasicRedis->isConnected() == false)
            {
                if($this->getRedisBasicCacheConfiguration()["UseCompression"])
                {
                    if (
                        (int)$this->getRedisBasicCacheConfiguration()["CompressionLevel"] < -1 ||
                        (int)$this->getRedisBasicCacheConfiguration()["CompressionLevel"] > 9
                    )
                        throw new RedisCacheException("Compression is enabled but the compression level must be a value between -1 & 9");

                    if (function_exists("gzcompress") == false)
                    {
                        throw new DependencyError("RedisBasiCache uses compression but the compression extension (zlib & php-zip) is not installed");
                    }
                }

                $this->BasicRedis->connect(
                    $this->getRedisBasicCacheConfiguration()["RedisHost"],
                    $this->getRedisBasicCacheConfiguration()["RedisPort"]
                );

                if($this->getRedisBasicCacheConfiguration()["UseAuthentication"])
                {
                    $this->BasicRedis->auth($this->getRedisBasicCacheConfiguration()["Password"]);
                }
            }
        }

        /**
         * Disconnects from the basic redis cache server
         */
        public function disconnectBasicRedis()
        {
            if($this->BasicRedis !== null && $this->BasicRedis->isConnected())
                $this->BasicRedis->close();
        }

        /**
         * @return Redis
         * @throws DependencyError
         * @throws RedisCacheException
         */
        public function getBasicRedis(): Redis
        {
            $this->connectBasicRedis();

            return $this->BasicRedis;
        }

        /**
         * @return BasicRedisCacheManager
         */
        public function getBasicRedisCacheManager(): BasicRedisCacheManager
        {
            if($this->BasicRedisCacheManager == null)
                $this->BasicRedisCacheManager = new BasicRedisCacheManager($this);

            return $this->BasicRedisCacheManager;
        }

        /**
         * @return TelegramCdnManager
         */
        public function getTelegramCdnManager(): TelegramCdnManager
        {
            if($this->TelegramCdnManager == null)
                $this->TelegramCdnManager = new TelegramCdnManager($this);

            return $this->TelegramCdnManager;
        }

        /**
         * @return mixed
         */
        public function getCdnConfiguration()
        {
            return $this->CdnConfiguration;
        }

        /**
         * @return mixed
         */
        public function getRpcServerConfiguration()
        {
            return $this->RpcServerConfiguration;
        }

        /**
         * @return DocumentsManager
         */
        public function getDocumentsManager(): DocumentsManager
        {
            if($this->DocumentsManager == null)
                $this->DocumentsManager = new DocumentsManager($this);

            return $this->DocumentsManager;
        }
    }
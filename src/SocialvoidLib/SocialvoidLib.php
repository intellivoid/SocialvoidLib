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

    use acm2\acm2;
    use acm2\Objects\Schema;
    use BackgroundWorker\BackgroundWorker;
    use BackgroundWorker\Exceptions\ServerNotReachableException;
    use Exception;
    use Longman\TelegramBot\Exception\TelegramException;
    use mysqli;
    use Redis;
    use SocialvoidLib\Classes\HealthMonitoring;
    use SocialvoidLib\Exceptions\GenericInternal\BackgroundWorkerNotEnabledException;
    use SocialvoidLib\Exceptions\GenericInternal\ConfigurationError;
    use SocialvoidLib\Exceptions\GenericInternal\DependencyError;
    use SocialvoidLib\Exceptions\GenericInternal\RedisCacheException;
    use SocialvoidLib\Managers\BasicRedisCacheManager;
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
    use SocialvoidLib\Managers\SlaveManager;
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
         * @var acm2
         */
        private acm2 $acm;

        /**
         * @var mixed
         */
        private $DatabaseConfiguration;

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
         * @var HealthMonitoring|null
         */
        private $HealthMonitoring;

        /**
         * @var mixed
         */
        private $SlaveServerConfiguration;

        /**
         * @var SlaveManager
         */
        private $SlaveManager;

        /**
         * @var mixed
         */
        private $MainConfiguration;

        /**
         * SocialvoidLib constructor.
         * @throws ConfigurationError
         * @throws DependencyError
         * @throws Exceptions\GenericInternal\DuplicateSlaveNameException
         * @throws Exceptions\GenericInternal\InvalidStringFormatException
         */
        public function __construct()
        {
            // Advanced Configuration Manager
            $this->acm = new acm2('SocialvoidLib');

            // Network Schema Configuration
            $MainSchema = new Schema();
            $MainSchema->setName('Main');
            $MainSchema->setDefinition('MainDomain', 'socialvoid.cc');
            $MainSchema->setDefinition('NetworkName', 'Socialvoid');
            $MainSchema->setDefinition('UnauthorizedSessionTTL', 600);
            $MainSchema->setDefinition('AuthorizedSessionTTL', 259200);
            $MainSchema->setDefinition('TimelineChunkSize', 20);
            $MainSchema->setDefinition('TimelineMaxSize', 3200);
            $MainSchema->setDefinition('TimelineMaxSize', 3200);
            $this->acm->defineSchema($MainSchema);

            // Database Schema Configuration
            $DatabaseSchema = new Schema();
            $DatabaseSchema->setName('Database');
            $DatabaseSchema->setDefinition('Host', '127.0.0.1');
            $DatabaseSchema->setDefinition('Port', '3306');
            $DatabaseSchema->setDefinition('Username', 'root');
            $DatabaseSchema->setDefinition('Password', '');
            $DatabaseSchema->setDefinition('Name', 'socialvoid');
            $this->acm->defineSchema($DatabaseSchema);

            // Slave Servers Schema Configuration
            $SlaveServerSchema = new Schema();
            $SlaveServerSchema->setName('SlaveServers');
            $SlaveServerSchema->setDefinition('MySqlSlaves', [
                'mysql://main:admin:admin@127.0.0.1:3306/socialvoid'
            ]);
            $this->acm->defineSchema($SlaveServerSchema);

            // RPC Schema Configuration
            $RpcSchema = new Schema();
            $RpcSchema->setName('RpcServer');
            $RpcSchema->setDefinition('EnableBackgroundWorker', True);
            $RpcSchema->setDefinition('Workers', 100);
            $RpcSchema->setDefinition('GearmanHost', '127.0.0.1');
            $RpcSchema->setDefinition('GearmanPort', 4730);
            $RpcSchema->setDefinition('ServerName', 'Socialvoid RPC');
            $RpcSchema->setDefinition('MaxRequests', 20);
            $this->acm->defineSchema($RpcSchema);

            // Service Engine Schema Configuration
            $ServiceEngineSchema = new Schema();
            $ServiceEngineSchema->setName('ServiceEngine');
            $ServiceEngineSchema->setDefinition('EnableBackgroundWorker', True);
            $ServiceEngineSchema->setDefinition('GearmanHost', '127.0.0.1');
            $ServiceEngineSchema->setDefinition('GearmanPort', 4730);
            $ServiceEngineSchema->setDefinition('QueryWorkers', 30);
            $ServiceEngineSchema->setDefinition('UpdateWorkers', 20);
            $ServiceEngineSchema->setDefinition('HeavyWorkers', 5);
            $ServiceEngineSchema->setDefinition('DisplayOutput', true);
            $this->acm->defineSchema($ServiceEngineSchema);

            // CDN Schema Configuration
            $CdnSchema = new Schema();
            $CdnSchema->setName('CDN');
            $CdnSchema->setDefinition('CdnEndpoint', 'https://cdn.socialvoid.cc');
            $CdnSchema->setDefinition('MaxFileUploadSize', 26214400); // 25 MB
            $CdnSchema->setDefinition('TelegramCdnEnabled', True);
            $CdnSchema->setDefinition('TelegramCdnEnabled', True);
            $CdnSchema->setDefinition('TelegramBotToken', '<BOT TOKEN>');
            $CdnSchema->setDefinition('TelegramChannels', []);
            $this->acm->defineSchema($CdnSchema);

            // Redis Basic Cache (Entity resolve cache)
            $RedisBasicCacheSchema = new Schema();
            $RedisBasicCacheSchema->setName('RedisBasicCache');
            $RedisBasicCacheSchema->setDefinition('Enabled', True);
            $RedisBasicCacheSchema->setDefinition('UseAuthentication', True);
            $RedisBasicCacheSchema->setDefinition('UseCompression', True);
            $RedisBasicCacheSchema->setDefinition('CompressionLevel', 9);
            $RedisBasicCacheSchema->setDefinition('PeerCacheEnabled', True);
            $RedisBasicCacheSchema->setDefinition('PeerCacheTTL', 500);
            $RedisBasicCacheSchema->setDefinition('PeerCacheLimit', 1000);
            $RedisBasicCacheSchema->setDefinition('PostCacheEnabled', True);
            $RedisBasicCacheSchema->setDefinition('PostCacheTTL', 300);
            $RedisBasicCacheSchema->setDefinition('PostCacheLimit', 1000);
            $RedisBasicCacheSchema->setDefinition('SessionCacheEnabled', True);
            $RedisBasicCacheSchema->setDefinition('SessionCacheTTL', 300);
            $RedisBasicCacheSchema->setDefinition('SessionCacheLimit', 1000);
            $RedisBasicCacheSchema->setDefinition('DocumentCacheEnabled', True);
            $RedisBasicCacheSchema->setDefinition('DocumentCacheTTL', 300);
            $RedisBasicCacheSchema->setDefinition('DocumentCacheLimit', 1000);
            $RedisBasicCacheSchema->setDefinition('TelegramCdnCacheEnabled', True);
            $RedisBasicCacheSchema->setDefinition('TelegramCdnCacheTTL', 300);
            $RedisBasicCacheSchema->setDefinition('TelegramCdnCacheLimit', 1000);
            $RedisBasicCacheSchema->setDefinition('RedisHost', '127.0.0.1');
            $RedisBasicCacheSchema->setDefinition('RedisPort', 6379);
            $RedisBasicCacheSchema->setDefinition('Password', 'admin');
            $this->acm->defineSchema($RedisBasicCacheSchema);

            // Data storage Schema Configuration
            $DataStorageSchema = new Schema();
            $DataStorageSchema->setName('DataStorage');
            $DataStorageSchema->setDefinition('UserAvatarsLocation', '/var/socialvoid/avatars');
            $DataStorageSchema->setDefinition('LegalDocumentsLocation', '/var/socialvoid/legal');
            $DataStorageSchema->setDefinition('WorkingLocation', '/var/socialvoid/lib');
            $this->acm->defineSchema($DataStorageSchema);

            // Save any changes
            $this->acm->updateConfiguration();

            try
            {
                $this->MainConfiguration = $this->acm->getConfiguration('Main');
                $this->DatabaseConfiguration = $this->acm->getConfiguration('Database');
                $this->DataStorageConfiguration = $this->acm->getConfiguration('DataStorage');
                $this->ServiceEngineConfiguration = $this->acm->getConfiguration('ServiceEngine');
                $this->RedisBasicCacheConfiguration = $this->acm->getConfiguration('RedisBasicCache');
                $this->CdnConfiguration = $this->acm->getConfiguration('CDN');
                $this->RpcServerConfiguration = $this->acm->getConfiguration('RpcServer');
                $this->SlaveServerConfiguration = $this->acm->getConfiguration('SlaveServers');
            }
            catch(Exception $e)
            {
                throw new ConfigurationError('There was an error while trying to load ACM', 0, $e);
            }

            $this->SlaveManager = new SlaveManager($this);

            // Initialize constants
            self::defineLibConstant('SOCIALVOID_LIB_TIMELINE_MAX_SIZE', $this->getMainConfiguration()['TimelineMaxSize']);
            self::defineLibConstant('SOCIALVOID_LIB_TIMELINE_CHUNK_SIZE', $this->getMainConfiguration()['TimelineChunkSize']);

            self::defineLibConstant('SOCIALVOID_LIB_BACKGROUND_WORKER_ENABLED', (bool)$this->getServiceEngineConfiguration()['EnableBackgroundWorker']);
            self::defineLibConstant('SOCIALVOID_LIB_BACKGROUND_QUERY_WORKERS', (int)$this->getServiceEngineConfiguration()['QueryWorkers']);
            self::defineLibConstant('SOCIALVOID_LIB_BACKGROUND_UPDATE_WORKERS', (int)$this->getServiceEngineConfiguration()['UpdateWorkers']);
            self::defineLibConstant('SOCIALVOID_LIB_BACKGROUND_HEAVY_WORKERS', (int)$this->getServiceEngineConfiguration()['HeavyWorkers']);

            self::defineLibConstant('SOCIALVOID_LIB_BASIC_CACHE_ENABLED', (bool)$this->getRedisBasicCacheConfiguration()['Enabled']);

            self::defineLibConstant('SOCIALVOID_LIB_NETWORK_DOMAIN', $this->getMainConfiguration()['MainDomain']);
            self::defineLibConstant('SOCIALVOID_LIB_NETWORK_NAME', $this->getMainConfiguration()['NetworkName']);

            // Initialize UDP
            try
            {
                $this->UserDisplayPictureManager = new udp2();
                $this->UserDisplayPictureManager->setStorageLocation($this->DataStorageConfiguration['UserAvatarsLocation']);
            }
            catch(Exception $e)
            {
                throw new DependencyError('There was an error while trying to initialize UDP', 0, $e);
            }

            if($this->getServiceEngineConfiguration()['EnableBackgroundWorker'] && function_exists('gearman_version') == false)
                throw new DependencyError('ServiceEngine has BackgroundWorker enabled but the gearman extension (php-gearman) is not installed.');
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
         * @return acm2
         */
        public function getAcm(): acm2
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
                $this->DatabaseConfiguration['Host'],
                $this->DatabaseConfiguration['Username'],
                $this->DatabaseConfiguration['Password'],
                $this->DatabaseConfiguration['Name'],
                $this->DatabaseConfiguration['Port']
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
         * @return SlaveManager
         */
        public function getSlaveManager(): SlaveManager
        {
            return $this->SlaveManager;
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
         * @throws ServerNotReachableException
         */
        public function getBackgroundWorker(): BackgroundWorker
        {
            if((bool)$this->getServiceEngineConfiguration()['EnableBackgroundWorker'] == false)
                throw new BackgroundWorkerNotEnabledException('BackgroundWorker is not enabled for this build');

            if($this->BackgroundWorker == null)
                $this->BackgroundWorker = new BackgroundWorker();

            if($this->BackgroundWorkerInitialized == false)
            {
                $this->BackgroundWorker->getClient()->addServer(
                    $this->getServiceEngineConfiguration()['GearmanHost'],
                    (int)$this->getServiceEngineConfiguration()['GearmanPort']
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

            if($this->getRedisBasicCacheConfiguration()['Enabled'] == false)
                throw new RedisCacheException('RedisBasicCache is not enabled');

            if(class_exists('Redis') == false)
                throw new DependencyError('RedisBasicCache is enabled but the Redis Extension (php-redis) is not installed');

            if($this->BasicRedis == null)
                $this->BasicRedis = new Redis();

            if($this->BasicRedis->isConnected() == false)
            {
                if($this->getRedisBasicCacheConfiguration()['UseCompression'])
                {
                    if (
                        (int)$this->getRedisBasicCacheConfiguration()['CompressionLevel'] < -1 ||
                        (int)$this->getRedisBasicCacheConfiguration()['CompressionLevel'] > 9
                    )
                        throw new RedisCacheException('Compression is enabled but the compression level must be a value between -1 & 9');

                    if (function_exists('gzcompress') == false)
                    {
                        throw new DependencyError('RedisBasiCache uses compression but the compression extension (zlib & php-zip) is not installed');
                    }
                }

                $this->BasicRedis->connect(
                    $this->getRedisBasicCacheConfiguration()['RedisHost'],
                    $this->getRedisBasicCacheConfiguration()['RedisPort']
                );

                if($this->getRedisBasicCacheConfiguration()['UseAuthentication'])
                {
                    $this->BasicRedis->auth($this->getRedisBasicCacheConfiguration()['Password']);
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
         * @throws TelegramException
         * @throws TelegramException
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

        /**
         * Returns the working directory path where multiple data points are stored at
         *
         * @return string
         */
        public function getWorkingLocationPath(): string
        {
            return $this->getDataStorageConfiguration()['WorkingLocation'];
        }

        /**
         * @return HealthMonitoring
         */
        public function getHealthMonitoring(): HealthMonitoring
        {
            if($this->HealthMonitoring == null)
                $this->HealthMonitoring = new HealthMonitoring($this);

            return $this->HealthMonitoring;
        }

        /**
         * @return mixed
         */
        public function getSlaveServerConfiguration()
        {
            return $this->SlaveServerConfiguration;
        }

        /**
         * @return mixed
         */
        public function getMainConfiguration()
        {
            return $this->MainConfiguration;
        }
    }
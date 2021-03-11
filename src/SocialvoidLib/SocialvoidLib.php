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
    use SocialvoidLib\Exceptions\GenericInternal\BackgroundWorkerNotEnabledException;
    use SocialvoidLib\Exceptions\GenericInternal\ConfigurationError;
    use SocialvoidLib\Exceptions\GenericInternal\DependencyError;
    use SocialvoidLib\Managers\FollowerDataManager;
    use SocialvoidLib\Managers\FollowerStateManager;
    use SocialvoidLib\Managers\LikesRecordManager;
    use SocialvoidLib\Managers\PostsManager;
    use SocialvoidLib\Managers\RepostsRecordManager;
    use SocialvoidLib\Managers\SessionManager;
    use SocialvoidLib\Managers\TimelineManager;
    use SocialvoidLib\Managers\UserManager;
    use udp\udp;

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
         * @var udp
         */
        private udp $UserDisplayPictureManager;

        /**
         * @var mysqli|null
         */
        private $database;

        /**
         * @var UserManager
         */
        private UserManager $UserManager;

        /**
         * @var FollowerStateManager
         */
        private FollowerStateManager $FollowerStateManager;

        /**
         * @var SessionManager
         */
        private SessionManager $SessionManager;

        /**
         * @var FollowerDataManager
         */
        private FollowerDataManager $FollowerDataManager;

        /**
         * @var mixed
         */
        private $EngineConfiguration;

        /**
         * @var PostsManager
         */
        private PostsManager $PostsManager;

        /**
         * @var BackgroundWorker
         */
        private BackgroundWorker $BackgroundWorker;

        /*
         * Indicates if background worker was initialized or not
         *
         * @var bool
         */
        private bool $BackgroundWorkerInitialized = false;

        /**
         * @var LikesRecordManager
         */
        private LikesRecordManager $LikesRecordManager;

        /**
         * @var RepostsRecordManager
         */
        private RepostsRecordManager $RepostsRecordManager;

        /**
         * @var TimelineManager
         */
        private TimelineManager $TimelineManager;

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
            $this->acm->defineSchema("Network", $NetworkSchema);

            // Engine Schema Configuration
            $EngineSchema = new Schema();
            $EngineSchema->setDefinition("MaxPeerResolveCacheCount", 20);
            $EngineSchema->setDefinition("EnableBackgroundWorker", True);
            $EngineSchema->setDefinition("EnableWorkerCache", True);
            $EngineSchema->setDefinition("GearmanHost", "127.0.0.1");
            $EngineSchema->setDefinition("GearmanPort", 4730);
            $EngineSchema->setDefinition("MaxWorkers", 30);
            $this->acm->defineSchema("Engine", $EngineSchema);

            // Data storage Schema Configuration
            $DataStorageSchema = new Schema();
            $DataStorageSchema->setDefinition("ProfilesLocation_Unix", "/etc/socialvoid_avatars");
            $DataStorageSchema->setDefinition("ProfilesLocation_Windows", "C:\\socialvoid_avatars");
            $this->acm->defineSchema("DataStorage", $DataStorageSchema);

            try
            {
                $this->DatabaseConfiguration = $this->acm->getConfiguration("Database");
                $this->NetworkConfiguration = $this->acm->getConfiguration("Network");
                $this->DataStorageConfiguration = $this->acm->getConfiguration("DataStorage");
                $this->EngineConfiguration = $this->acm->getConfiguration("Engine");
            }
            catch(Exception $e)
            {
                throw new ConfigurationError("There was an error while trying to load ACM", 0, $e);
            }

            // Initialize constants
            self::defineLibConstant("SOCIALVOID_LIB_MAX_PEER_RESOLVE_CACHE_COUNT", $this->getEngineConfiguration()["MaxPeerResolveCacheCount"]);
            self::defineLibConstant("SOCIALVOID_LIB_CACHE_ENABLED", (bool)$this->getEngineConfiguration()["EnableWorkerCache"]);
            self::defineLibConstant("SOCIALVOID_LIB_BACKGROUND_WORKER_ENABLED", (bool)$this->getEngineConfiguration()["EnableBackgroundWorker"]);
            self::defineLibConstant("SOCIALVOID_LIB_BACKGROUND_WORKERS_AVAILABLE", (int)$this->getEngineConfiguration()["MaxWorkers"]);

            // Initialize UDP
            try
            {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
                {
                    $this->UserDisplayPictureManager = new udp($this->DataStorageConfiguration['ProfilesLocation_Windows']);
                }
                else
                {
                    $this->UserDisplayPictureManager = new udp($this->DataStorageConfiguration['ProfilesLocation_Unix']);
                }
            }
            catch(Exception $e)
            {
                throw new DependencyError("There was an error while trying to initialize UDP", 0, $e);
            }

            $this->UserManager = new UserManager($this);
            $this->FollowerStateManager = new FollowerStateManager($this);
            $this->SessionManager = new SessionManager($this);
            $this->FollowerDataManager = new FollowerDataManager($this);
            $this->PostsManager = new PostsManager($this);
            $this->LikesRecordManager = new LikesRecordManager($this);
            $this->RepostsRecordManager = new RepostsRecordManager($this);
            $this->TimelineManager = new TimelineManager($this);
            $this->BackgroundWorker = new BackgroundWorker();
        }

        /**
         * Defines a library constant
         *
         * @param string $name
         * @param $value
         * @return bool
         */
        private static function defineLibConstant(string $name, $value): bool
        {
            if(defined($name))
                return false;

            define($name, $value);
            return true;
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
         * @return udp
         */
        public function getUserDisplayPictureManager(): udp
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
            return $this->UserManager;
        }

        /**
         * @return FollowerStateManager
         */
        public function getFollowerStateManager(): FollowerStateManager
        {
            return $this->FollowerStateManager;
        }

        /**
         * @return SessionManager
         */
        public function getSessionManager(): SessionManager
        {
            return $this->SessionManager;
        }

        /**
         * @return FollowerDataManager
         */
        public function getFollowerDataManager(): FollowerDataManager
        {
            return $this->FollowerDataManager;
        }

        /**
         * @return mixed
         */
        public function getEngineConfiguration()
        {
            return $this->EngineConfiguration;
        }

        /**
         * @return PostsManager
         */
        public function getPostsManager(): PostsManager
        {
            return $this->PostsManager;
        }

        /**
         * Returns background worker and initalizes it if it's not already initialized
         *
         * @return BackgroundWorker
         * @throws BackgroundWorkerNotEnabledException
         */
        public function getBackgroundWorker(): BackgroundWorker
        {
            if((bool)$this->getEngineConfiguration()["EnableBackgroundWorker"] == false)
                throw new BackgroundWorkerNotEnabledException("BackgroundWorker is not enabled for this build");

            if($this->BackgroundWorkerInitialized == false)
            {
                $this->BackgroundWorker->getClient()->addServer(
                    $this->getEngineConfiguration()["GearmanHost"],
                    (int)$this->getEngineConfiguration()["GearmanPort"]
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
            return $this->LikesRecordManager;
        }

        /**
         * @return RepostsRecordManager
         */
        public function getRepostsRecordManager(): RepostsRecordManager
        {
            return $this->RepostsRecordManager;
        }

        /**
         * @return TimelineManager
         */
        public function getTimelineManager(): TimelineManager
        {
            return $this->TimelineManager;
        }
    }
<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidRPC;

    use KimchiRPC\Exceptions\MethodAlreadyRegistered;
    use KimchiRPC\KimchiRPC;
    use RuntimeException;
    use SocialvoidLib\SocialvoidLib;
    use SocialvoidRPC\Methods\Session\CreateSession;
    use VerboseAdventure\Abstracts\EventType;
    use VerboseAdventure\VerboseAdventure;

    /**
     * Class SocialvoidRPC
     * @package SocialvoidRPC
     */
    class SocialvoidRPC
    {
        /**
         * @var SocialvoidLib
         */
        public static $SocialvoidLib;

        /**
         * The last Unix Timestamp when the worker was invoked
         *
         * @var int
         */
        public static $LastWorkerActivity;

        /**
         * Indicates if this worker is sleeping
         *
         * @var bool
         */
        public static $IsSleeping;

        /**
         * @var VerboseAdventure
         */
        public static $LogHandler;

        /**
         * @var KimchiRPC
         */
        public static $RpcServer;

        /**
         * @return VerboseAdventure
         */
        public static function getLogHandler(): VerboseAdventure
        {
            return self::$LogHandler;
        }

        /**
         * @param VerboseAdventure $LogHandler
         */
        public static function setLogHandler(VerboseAdventure $LogHandler): void
        {
            self::$LogHandler = $LogHandler;
        }

        /**
         * @return int
         */
        public static function getLastWorkerActivity(): int
        {
            return self::$LastWorkerActivity;
        }

        /**
         * @param int $LastWorkerActivity
         */
        public static function setLastWorkerActivity(int $LastWorkerActivity): void
        {
            self::$LastWorkerActivity = $LastWorkerActivity;
        }

        /**
         * @return bool
         */
        public static function isSleeping(): bool
        {
            return self::$IsSleeping;
        }

        /**
         * @param bool $IsSleeping
         */
        public static function setIsSleeping(bool $IsSleeping): void
        {
            self::$IsSleeping = $IsSleeping;
        }

        /**
         * Registers the servers methods to the RPC server
         *
         * @throws MethodAlreadyRegistered
         */
        public static function registerMethods()
        {
            if(self::$RpcServer == null)
                throw new RuntimeException("No RPC Server has been defined");

            self::$RpcServer->registerMethod(new CreateSession());
        }

        /**
         * @return SocialvoidLib
         */
        public static function getSocialvoidLib(): SocialvoidLib
        {
            return self::$SocialvoidLib;
        }

        /**
         * Wakes up the worker if it's sleeping
         */
        public static function processWakeup()
        {
            SocialvoidRPC::setLastWorkerActivity((int)time()); // Set the last activity timestamp
            SocialvoidRPC::processSleepCycle(); // Wake worker if it's sleeping
        }

        /**
         * Determines if this current worker should save resources by going to sleep or wake up depending on the
         * last activity cycle
         */
        public static function processSleepCycle()
        {
            if(time() - self::getLastWorkerActivity() > 60)
            {
                if(self::isSleeping() == false)
                {
                    self::getLogHandler()->log(EventType::INFO, "RPC Worker hasn't been active the last 60 seconds, going to sleep.", "Service Worker");

                    self::getSocialvoidLib()->disconnectDatabase();
                    self::setIsSleeping(true);
                }
            }
            else
            {
                if(self::isSleeping() == true)
                {
                    self::getLogHandler()->log(EventType::INFO, "RPC Worker is active, awaking from sleep mode", "Service Worker");
                    self::getSocialvoidLib()->connectDatabase();
                    self::setIsSleeping(false);
                }
            }
        }
    }
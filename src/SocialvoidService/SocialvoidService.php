<?php

    /** @noinspection ALL */
    /** @noinspection ALL */
    /** @noinspection ALL */
    /** @noinspection ALL */
    /** @noinspection ALL */

    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

        namespace SocialvoidService;

    use BackgroundWorker\BackgroundWorker;
    use SocialvoidLib\Abstracts\StatusStates\HealthStatusCode;
    use SocialvoidLib\SocialvoidLib;
    use VerboseAdventure\Abstracts\EventType;
    use VerboseAdventure\VerboseAdventure;

    /**
     * Class SocialvoidService
     * @package SocialvoidService
     */
    class SocialvoidService
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
         * @var BackgroundWorker
         */
        public static $BackgroundWorker;

        /**
         * @var VerboseAdventure
         */
        public static $LogHandler;

        /**
         * @return BackgroundWorker
         */
        public static function getBackgroundWorker(): BackgroundWorker
        {
            return self::$BackgroundWorker;
        }

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
            SocialvoidService::setLastWorkerActivity((int)time()); // Set the last activity timestamp
            SocialvoidService::processSleepCycle(); // Wake worker if it's sleeping
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
                    self::getSocialvoidLib()->disconnectDatabase();
                    self::setIsSleeping(true);
                }
            }
            else
            {
                if(self::isSleeping() == true)
                {
                    self::getSocialvoidLib()->connectDatabase();
                    self::setIsSleeping(false);
                }
            }
        }

    }
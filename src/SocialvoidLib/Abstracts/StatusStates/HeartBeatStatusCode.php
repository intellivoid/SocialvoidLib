<?php

    namespace SocialvoidLib\Abstracts\StatusStates;

    abstract class HeartBeatStatusCode
    {
        /**
         * Indicates that the module is starting and isn't ready yet
         */
        const Starting = 0;

        /**
         * Indicates that the module is running correctly
         */
        const Ok = 1;

        /**
         * Indicates that the module is running but with issues
         */
        const Failing = 2;

        /**
         * Indicates that the module is not running and has stopped due to a fatal error
         */
        const Fatal = 3;

        /**
         * Indicates that the module has terminated
         */
        const Terminated = 4;
    }
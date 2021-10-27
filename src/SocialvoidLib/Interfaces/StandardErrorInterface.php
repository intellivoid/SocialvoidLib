<?php

    namespace SocialvoidLib\Interfaces;

    use SocialvoidLib\Objects\Definitions\ErrorDefinition;

    interface StandardErrorInterface
    {
        /**
         * Returns the name of the standard error
         *
         * @return string
         */
        public static function getName(): string;

        /**
         * Returns a description of the standard error
         *
         * @return string
         */
        public static function getDescription(): string;

        /**
         * Returns the standard error code used
         *
         * @return int
         */
        public static function getErrorCode(): int;

        /**
         * Returns an error definition object of the standard error
         *
         * @return ErrorDefinition
         */
        public static function getDefinition(): ErrorDefinition;
    }
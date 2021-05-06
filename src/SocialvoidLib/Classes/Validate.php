<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

namespace SocialvoidLib\Classes;

    use SocialvoidLib\Abstracts\StandardErrorCodeType;

    /**
     * Class Validation
     * @package SocialvoidLib\Classes
     */
    class Validate
    {
        /**
         * Validates if the given username is valid or not.
         *
         * @param string $input
         * @return bool
         */
        public static function username(string $input): bool
        {
            preg_match('/^(?!_)[A-Za-z0-9_]{3,32}(?<!_)$/m', $input, $matches);
            return count($matches) >= 1 && $matches !== false;
        }

        /**
         * Validates if the given password is safe or not.
         *
         * @param string $input
         * @return bool
         */
        public static function password(string $input): bool
        {
            /** @noinspection RegExpRedundantEscape */
            preg_match('/^(?=.*[A-Z])(?=.*\d.*\d)(?=.*[ -\/:-@\[-`\{-~])[ -~]{12,128}$/m', $input, $matches);
            return count($matches) >= 1 && $matches !== false;
        }

        /**
         * Validates the first name
         *
         * @param string|null $input
         * @return bool
         */
        public static function firstName(string $input=null): bool
        {
            if (strlen($input) == 0)
                return false;

            if (strlen($input) > 64)
                return false;

            return true;
        }

        /**
         * Validates the last name
         *
         * @param string|null $input
         * @return bool
         */
        public static function lastName(string $input=null): bool
        {
            if (strlen($input) > 64)
                return false;

            return true;
        }

        /**
         * Validates the user biography
         *
         * @param string|null $input
         * @return bool
         * @noinspection PhpUnused
         */
        public static function biography(string $input=null): bool
        {
            if(strlen($input) > 255)
                return false;

            return true;
        }

        /**
         * Determines the standard error code type by checking the error code range
         *
         * @param int $error_code
         * @return string
         */
        public static function determineStandardErrorType(int $error_code): string
        {
            /**
             * 31-Set error codes (Network)
             * 12544 - *
             */
            if($error_code >= 12544)
            {
                return StandardErrorCodeType::NetworkError;
            }

            /**
             * 23-Set Error codes (Media)
             * 8960 - 12543
             */
            if($error_code >= 8960)
            {
                return StandardErrorCodeType::AuthenticationError;
            }

            /**
             * 22-Set Error codes (Authentication)
             * 8704 - 8960
             */
            if($error_code >= 8704)
            {
                return StandardErrorCodeType::AuthenticationError;
            }

            /**
             * 21-Set Error codes (Validation)
             * 8448 - 8703
             */
            if($error_code >= 8448)
            {
                return StandardErrorCodeType::ValidationError;
            }

            return StandardErrorCodeType::Unknown;
        }

        /**
         * Determines if the error code is a standard to Socialvoid or not
         *
         * @param int $error_code
         * @return bool
         * @noinspection PhpUnused
         */
        public static function isStandardError(int $error_code): bool
        {
            if(self::determineStandardErrorType($error_code) == StandardErrorCodeType::Unknown)
            {
                return false;
            }

            return true;
        }
    }
<?php


    namespace SocialvoidLib\Classes;

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
            preg_match('/^(?=.*[A-Z])(?=.*\d.*\d)(?=.*[ -\/:-@\[-`\{-~])[ -~]{12,128}$/m', $input, $matches);
            return count($matches) >= 1 && $matches !== false;
        }

        /**
         * Validates the first name
         *
         * @param string $input
         * @return bool
         */
        public static function firstName(string $input): bool
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
         * @param string $input
         * @return bool
         */
        public static function lastName(string $input): bool
        {
            if (strlen($input) > 64)
                return false;

            return true;
        }

        /**
         * Validates the user biography
         *
         * @param string $input
         * @return bool
         */
        public static function biography(string $input): bool
        {
            if(strlen($input) > 255)
                return false;

            return true;
        }
    }
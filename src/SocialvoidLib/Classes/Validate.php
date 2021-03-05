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
            preg_match('/^(?!_)[A-Za-z0-9_]{3,20}(?<!_)$/m', $input, $matches);
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
            // Return true for now
            return true;
        }
    }
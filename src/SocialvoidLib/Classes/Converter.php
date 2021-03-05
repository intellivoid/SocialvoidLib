<?php


    namespace SocialvoidLib\Classes;

    /**
     * Class Converter
     * @package SocialvoidLib\Classes
     */
    class Converter
    {
        /**
         * Converts an empty string to a null value, if empty.
         *
         * @param string $input
         * @return string|null
         */
        public static function emptyString(string $input): ?string
        {
            if(strlen($input) == 0)
                return null;

            return $input;
        }
    }
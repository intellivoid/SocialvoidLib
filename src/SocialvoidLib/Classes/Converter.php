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
         * @param string|null $input
         * @return string|null
         */
        public static function emptyString(string $input=null): ?string
        {
            if($input == null)
                return null;

            if(strlen($input) == 0)
                return null;

            return $input;
        }

        /**
         * Adds a flag to the flag object
         *
         * @param array $flags
         * @param $flag
         */
        public static function addFlag(array &$flags, $flag): void
        {
            if(in_array($flag, $flags))
                return;

            $flags[] = $flag;
        }

        /**
         * Removes a flag from the flag object
         *
         * @param array $flags
         * @param $flag
         */
        public static function removeFlag(array &$flags, $flag): void
        {
            if(in_array($flag, $flags) == false)
                return;

            $flags = array_diff($flags, [$flag]);
        }

        /**
         * Determines if a flag set has a flag
         *
         * @param array $flags
         * @param mixed $flag
         * @return bool
         */
        public static function hasFlag(array &$flags, $flag): bool
        {
            if(is_array($flag))
            {
                foreach($flag as $value)
                {
                    if(in_array($value, $flags))
                        return true;
                }

                return false;
            }

            return in_array($flag, $flags);
        }
    }
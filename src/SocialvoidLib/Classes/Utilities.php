<?php


    namespace SocialvoidLib\Classes;

    use SocialvoidLib\Classes\Security\Hashing;

    /**
     * Class Utilities
     * @package SocialvoidLib\Classes
     */
    class Utilities
    {
        /**
         * Determines the a boolean definition, returns the default value if all else fails
         *
         * @param string $name
         * @param bool $default_value
         * @return bool
         */
        public static function getBoolDefinition(string $name, bool $default_value=false): bool
        {
            if(defined($name))
                return (bool)constant($name);

            return $default_value;
        }

        /**
         * Generates a job ID
         *
         * @param array $data
         * @param int $timestamp
         * @return string
         */
        public static function generateJobID(array $data, int $timestamp): string
        {
            $pepper = Hashing::pepper(json_encode($data) . $timestamp);
            return hash("sha256", $pepper . json_encode($data) . $timestamp);
        }
    }
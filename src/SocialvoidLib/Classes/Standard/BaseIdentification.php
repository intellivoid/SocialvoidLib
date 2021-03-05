<?php


    namespace SocialvoidLib\Classes\Standard;

    use SocialvoidLib\Classes\Security\Hashing;

    /**
     * Class BaseIdentification
     * @package SocialvoidLib\Classes\Standard
     */
    class BaseIdentification
    {
        /**
         * Generates a user Public ID using a pepper formula
         *
         * @param int $unix_timestamp
         * @return string
         */
        public static function UserPublicID(int $unix_timestamp): string
        {
            $a = hash("sha256", $unix_timestamp) . Hashing::pepper($unix_timestamp);
            return hash("sha256", Hashing::pepper($a . $unix_timestamp));
        }
    }
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

        /**
         * Generates a unique Following state ID
         *
         * @param int $user_id
         * @param int $target_user_id
         * @return string
         */
        public static function FollowingStateID(int $user_id, int $target_user_id): string
        {
            return hash("sha256", hash("crc32b", $user_id) . hash("crc32b", $target_user_id));
        }
    }
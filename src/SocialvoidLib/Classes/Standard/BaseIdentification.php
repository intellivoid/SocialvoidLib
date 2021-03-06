<?php


    namespace SocialvoidLib\Classes\Standard;

    use SocialvoidLib\Classes\Security\Hashing;
    use SocialvoidLib\InputTypes\SessionClient;
    use SocialvoidLib\InputTypes\SessionDevice;

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

        /**
         * Construts a random Session ID based off the given information
         *
         * @param int $user_id
         * @param SessionClient $session_client
         * @param SessionDevice $session_device
         * @param int $timestamp
         * @return string
         */
        public static function SessionID(int $user_id, SessionClient $session_client, SessionDevice $session_device, int $timestamp): string
        {
            $client_hash = hash("sha256", json_encode($session_client->toArray()));
            $device_hash = hash("sha256", json_encode($session_device->toArray()));
            $entity_pepper = Hashing::pepper($client_hash . $device_hash);

            $user_hash = hash("sha256", Hashing::pepper($user_id));
            return hash("sha512", $client_hash . $device_hash . $user_hash . $entity_pepper);
        }
    }
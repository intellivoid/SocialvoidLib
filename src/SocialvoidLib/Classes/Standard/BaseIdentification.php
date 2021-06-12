<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Classes\Standard;

    use SocialvoidLib\Classes\Security\Hashing;
    use SocialvoidLib\InputTypes\DocumentInput;
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
        public static function userPublicId(int $unix_timestamp): string
        {
            $a = hash("sha256", $unix_timestamp) . Hashing::pepper($unix_timestamp);
            return hash("sha256", Hashing::pepper($a . $unix_timestamp));
        }

        /**
         * Constructs a random Session ID based off the given information
         *
         * @param int $user_id
         * @param SessionClient $session_client
         * @param SessionDevice $session_device
         * @return string
         */
        public static function sessionId(int $user_id, SessionClient $session_client, SessionDevice $session_device): string
        {
            $client_hash = hash("sha256", json_encode($session_client->toArray()));
            $device_hash = hash("sha256", json_encode($session_device->toArray()));
            $entity_pepper = Hashing::pepper($client_hash . $device_hash);

            $user_hash = hash("sha256", Hashing::pepper($user_id));
            return hash("sha512", $client_hash . $device_hash . $user_hash . $entity_pepper);
        }

        /**
         * Returns a Post Base ID
         *
         * @param int $user_id
         * @param int $timestamp
         * @param string $text
         * @return string
         */
        public static function postId(int $user_id, int $timestamp, string $text): string
        {
            $user_hash = hash("sha256", $user_id . $timestamp);
            return hash("sha256", $user_id . Hashing::pepper($user_hash . $text));
        }

        /**
         * Returns a random, unique Document Public ID
         *
         * @param DocumentInput $documentInput
         * @return string
         */
        public static function documentId(DocumentInput $documentInput): string
        {
            return hash("sha256",
                Hashing::pepper($documentInput->OwnerUserID . $documentInput->ContentSource, time()) .
                $documentInput->OwnerUserID);
        }
    }
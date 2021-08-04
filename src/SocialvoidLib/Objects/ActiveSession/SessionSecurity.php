<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\ActiveSession;

    /**
     * Class SessionSecurity
     * @package SocialvoidLib\Objects\ActiveSession
     */
    class SessionSecurity
    {
        /**
         * A public hash of the client, must be 64 characters in length and must only contain alphanumeric characters.
         *
         * @var string
         */
        public $ClientPublicHash;

        /**
         * The private hash of the client,
         *
         * @var string
         */
        public $ClientPrivateHash;

        /**
         * The challenge that the user must complete with it's secret hash
         *
         * @var string
         */
        public $HashChallenge;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "client_public_hash" => $this->ClientPublicHash,
                "client_private_hash" => $this->ClientPrivateHash,
                "hash_challenge" => $this->HashChallenge
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return SessionSecurity
         */
        public static function fromArray(array $data): SessionSecurity
        {
            $session_security_object = new SessionSecurity();

            if(isset($data["client_public_hash"]))
                $session_security_object->ClientPublicHash = $data["client_public_hash"];

            if(isset($data["client_private_hash"]))
                $session_security_object->ClientPrivateHash = $data["client_private_hash"];

            if(isset($data["hash_challenge"]))
                $session_security_object->HashChallenge = $data["hash_challenge"];

            return $session_security_object;
        }
    }
<?php

    /** @noinspection PhpMissingFieldTypeInspection */


    namespace SocialvoidLib\Objects\ActiveSession\SessionCache;

    use SocialvoidLib\Objects\User;

    /**
     * Class PeerResolveCache
     * @package SocialvoidLib\Objects\ActiveSession\SessionCache
     */
    class PeerResolveCache
    {
        /**
         * The peer's ID
         *
         * @var int
         */
        public $PeerID;

        /**
         * The peer's Public ID
         *
         * @var string
         */
        public $PeerPublicID;

        /**
         * The peer's username
         *
         * @var string
         */
        public $PeerUsername;

        /**
         * The safe version of the peer's username
         *
         * @var string
         */
        public $PeerUsernameSafe;

        /**
         * The Unix Timestamp for when this record was last updated
         *
         * @var int
         */
        public $LastUpdatedTimestamp;

        /**
         * @return array
         */
        public function toArray(): array
        {
            return [
                0x001 => $this->PeerID,
                0x002 => $this->PeerPublicID,
                0x003 => $this->PeerUsername,
                0x004 => $this->PeerUsernameSafe,
                0x005 => $this->LastUpdatedTimestamp
            ];
        }

        /**
         * Constructs an object from an User object
         *
         * @param User $user
         * @return PeerResolveCache
         */
        public static function fromUser(User $user): PeerResolveCache
        {
            $PeerResolveCache = new PeerResolveCache();

            $PeerResolveCache->PeerID = $user->ID;
            $PeerResolveCache->PeerPublicID = $user->PublicID;
            $PeerResolveCache->PeerUsername = $user->Username;
            $PeerResolveCache->PeerUsernameSafe = $user->UsernameSafe;
            $PeerResolveCache->LastUpdatedTimestamp = $user->LastActivityTimestamp;

            return $PeerResolveCache;
        }

        /**
         * Constructs object from an array representations
         *
         * @param array $data
         * @return PeerResolveCache
         */
        public static function fromArray(array $data): PeerResolveCache
        {
            $PeerResolveCacheObject = new PeerResolveCache();

            if(isset($data[0x001]))
                $PeerResolveCacheObject->PeerID = $data[0x001];

            if(isset($data[0x002]))
                $PeerResolveCacheObject->PeerPublicID = $data[0x002];

            if(isset($data[0x003]))
                $PeerResolveCacheObject->PeerUsername = $data[0x003];

            if(isset($data[0x004]))
                $PeerResolveCacheObject->PeerUsernameSafe = $data[0x004];

            if(isset($data[0x005]))
                $PeerResolveCacheObject->LastUpdatedTimestamp = $data[0x005];

            return $PeerResolveCacheObject;
        }
    }
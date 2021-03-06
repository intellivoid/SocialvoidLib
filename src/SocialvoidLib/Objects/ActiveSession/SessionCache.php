<?php


    namespace SocialvoidLib\Objects\ActiveSession;

    /**
     * Class SessionCache
     * @package SocialvoidLib\Objects\ActiveSession
     */
    class SessionCache
    {
        /**
         * Returns an array representation of this object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return SessionCache
         */
        public static function fromArray(array $data): SessionCache
        {
            $SessionCacheObject = new SessionCache();

            return $SessionCacheObject;
        }
    }
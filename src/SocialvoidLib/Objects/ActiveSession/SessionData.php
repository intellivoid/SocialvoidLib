<?php

    /** @noinspection PhpUnused */

namespace SocialvoidLib\Objects\ActiveSession;

    /**
     * Class SessionData
     * @package SocialvoidLib\Objects\ActiveSession
     */
    class SessionData
    {
        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return SessionData
         */
        public static function fromArray(array $data): SessionData
        {
            $SessionDataObject = new SessionData();

            return $SessionDataObject;
        }
    }
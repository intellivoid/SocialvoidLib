<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\User;

    /**
     * Class CoaUserEntity
     * @package SocialvoidLib\Objects\User
     */
    class CoaUserEntity
    {
        /**
         * The unique ID for this users account
         *
         * @var string
         */
        public $ID;

        /**
         * The username of this users account
         *
         * @var string
         */
        public $Username;

        /**
         * The URL of avatars available from this COA User.
         *
         * @var CoaAvatar[]
         */
        public $Avatars;

        public function avatarsToArray(): array
        {
            $results = [];
            foreach($this->Avatars as $coaAvatar)
                $results[] = $coaAvatar->toArray();

            return $results;
        }

        /**
         * Returns an array representation of the objects
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "id" => $this->ID,
                "username" => $this->Username,
                "avatars" => $this->avatarsToArray()
            ];
        }
    }
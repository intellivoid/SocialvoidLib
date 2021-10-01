<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\User;

    /**
     * Class Name
     * @package SocialvoidLib\Objects\User
     */
    class Profile
    {
        /**
         * Indicates if the user avatar is visible or not, this will automatically be updated
         *
         * @var bool|null
         */
        public $UserAvatarVisible;

        /**
         * The first name of the user
         *
         * @var string|null
         */
        public $FirstName;

        /**
         * The last name of the user
         *
         * @var string|null
         */
        public $LastName;

        /**
         * The biography of the user (Description)
         *
         * @var string|null
         */
        public $Biography;

        /**
         * The location of the user
         *
         * @var string|null
         */
        public $Location;

        /**
         * The URL of the user's website
         *
         * @var string
         */
        public $URL;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "user_avatar_visible" => $this->UserAvatarVisible,
                "first_name" => $this->FirstName,
                "last_name" => $this->LastName,
                "biography" => $this->Biography,
                "location" => $this->Location,
                "url" => $this->URL
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return Profile
         */
        public static function fromArray(array $data): Profile
        {
            $ProfileObject = new Profile();

            if(isset($data["user_avatar_visible"]))
                $ProfileObject->UserAvatarVisible = $data["user_avatar_visible"];

            if(isset($data["first_name"]))
                $ProfileObject->FirstName = $data["first_name"];

            if(isset($data["last_name"]))
                $ProfileObject->LastName = $data["last_name"];

            if(isset($data["biography"]))
                $ProfileObject->Biography = $data["biography"];

            if(isset($data["location"]))
                $ProfileObject->Location = $data["location"];

            if(isset($data["url"]))
                $ProfileObject->URL = $data["url"];

            return $ProfileObject;
        }

    }
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

    namespace SocialvoidLib\Objects\Standard\Peer;

    /**
     * Class Profile
     * @package SocialvoidLib\Objects\Standard\Profile
     */
    class Profile
    {
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
         * An array of associative links
         *
         * @var string[]
         */
        public $Urls;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            $Urls = $this->Urls;
            if(count($this->Urls) == 0)
                $Urls = null;

            return [
                "first_name" => $this->FirstName,
                "last_name" => $this->LastName,
                "biography" => $this->Biography,
                "location" => $this->Location,
                "urls" => $Urls
            ];
        }

        /**
         * Constructs an object from an profile object (Lib)
         *
         * @param \SocialvoidLib\Objects\User\Profile $profile
         * @return Profile
         */
        public static function fromProfile(\SocialvoidLib\Objects\User\Profile $profile): Profile
        {
            $ProfileObject = new Profile();

            $ProfileObject->FirstName = $profile->FirstName;
            $ProfileObject->LastName = $profile->LastName;
            $ProfileObject->Biography = $profile->Biography;
            $ProfileObject->Location = $profile->Location;
            $ProfileObject->Urls = $profile->getUrls();

            return $ProfileObject;
        }

        /**
         * Constructs an object from an array representation
         *
         * @param array $data
         * @return Profile
         * @noinspection DuplicatedCode
         */
        public static function fromArray(array $data): Profile
        {
            $ProfileObject = new Profile();

            if(isset($data["first_name"]))
                $ProfileObject->FirstName = $data["first_name"];

            if(isset($data["last_name"]))
                $ProfileObject->LastName = $data["last_name"];

            if(isset($data["biography"]))
                $ProfileObject->Biography = $data["biography"];

            if(isset($data["location"]))
                $ProfileObject->Location = $data["location"];

            if(isset($data["urls"]))
                $ProfileObject->Urls = $data["urls"];

            return $ProfileObject;
        }
    }
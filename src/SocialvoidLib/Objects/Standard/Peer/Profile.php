<?php

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
            return [
                "first_name" => $this->FirstName,
                "last_name" => $this->LastName,
                "biography" => $this->Biography,
                "location" => $this->Location,
                "urls" => $this->Urls
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
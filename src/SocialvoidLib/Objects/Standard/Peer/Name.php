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

    use SocialvoidLib\Objects\User\Profile;

    /**
     * Class Name
     * @package SocialvoidLib\Objects\Standard\Name
     */
    class Name
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
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "first_name" => $this->FirstName,
                "last_name" => $this->LastName,
            ];
        }

        /**
         * Constructs an object from an profile object (Lib)
         *
         * @param Profile $profile
         * @return Name
         */
        public static function fromProfile(Profile $profile): Name
        {
            $ProfileObject = new Name();

            $ProfileObject->FirstName = $profile->FirstName;
            $ProfileObject->LastName = $profile->LastName;

            return $ProfileObject;
        }

        /**
         * Constructs an object from an array representation
         *
         * @param array $data
         * @return Name
         */
        public static function fromArray(array $data): Name
        {
            $ProfileObject = new Name();

            if(isset($data["first_name"]))
                $ProfileObject->FirstName = $data["first_name"];

            if(isset($data["last_name"]))
                $ProfileObject->LastName = $data["last_name"];

            return $ProfileObject;
        }
    }
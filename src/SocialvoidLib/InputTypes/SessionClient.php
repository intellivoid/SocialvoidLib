<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    /** @noinspection PhpMissingFieldTypeInspection */
    /** @noinspection PhpUnused */

    // TODO: Check if the data in the object is valid or not

    namespace SocialvoidLib\InputTypes;

    /**
     * Class SessionClient
     * @package SocialvoidLib\InputTypes
     */
    class SessionClient
    {
        /**
         * The client name
         *
         * @var string|null
         */
        public $Name;

        /**
         * The version of the client
         *
         * @var string|null
         */
        public $Version;

        /**
         * The platform that the client is running on
         *
         * @var string|null
         */
        public $Platform;

        /**
         * The public hash of the client
         *
         * @var string
         */
        public $PublicHash;

        /**
         * The private hash of the client
         *
         * @var string
         */
        public $PrivateHash;

        /**
         * Returns an array representation of the object
         *
         * @return array
         * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
         */
        public function toArray(): array
        {
            return [
                "name" => $this->Name,
                "version" => $this->Version,
                "platform" => $this->Platform,
                "public_hash" => $this->PublicHash,
                "private_hash" => $this->PrivateHash
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return SessionClient
         * @noinspection PhpPureAttributeCanBeAddedInspection
         */
        public static function fromArray(array $data): SessionClient
        {
            $SessionClientObject = new SessionClient();

            if(isset($data["name"]))
                $SessionClientObject->Name = $data["name"];

            if(isset($data["version"]))
                $SessionClientObject->Version = $data["version"];

            if(isset($data["platform"]))
                $SessionClientObject->Platform = $data["platform"];

            if(isset($data["public_hash"]))
                $SessionClientObject->PublicHash = $data["public_hash"];

            if(isset($data["private_hash"]))
                $SessionClientObject->PrivateHash = $data["private_hash"];

            return $SessionClientObject;
        }
    }
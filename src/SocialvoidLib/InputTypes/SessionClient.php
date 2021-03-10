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

    namespace SocialvoidLib\InputTypes;

    /**
     * Class SessionClient
     * @package SocialvoidLib\InputTypes
     */
    class SessionClient
    {
        /**
         * @var string|null
         */
        public $Name;

        /**
         * @var string|null
         */
        public $Version;

        /**
         * SessionClient constructor.
         * @param string|null $name
         * @param string|null $version
         */
        public function __construct(string $name=null, string $version=null)
        {
            $this->Name = $name;
            $this->Version = $version;
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "client_name" => $this->Name,
                "client_version" => $this->Version
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return SessionClient
         */
        public static function fromArray(array $data): SessionClient
        {
            $SessionClientObject = new SessionClient();

            if(isset($data["client_name"]))
                $SessionClientObject->Name = $data["client_name"];

            if(isset($data["client_version"]))
                $SessionClientObject->Version = $data["client_version"];

            return $SessionClientObject;
        }
    }
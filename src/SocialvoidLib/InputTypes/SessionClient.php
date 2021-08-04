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

    use SocialvoidLib\Abstracts\RegexPatterns;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPrivateHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPublicHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPlatformException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidVersionException;

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

        /**
         * Validates the object's properties and throws an exception if something is not correct
         *
         * @throws InvalidClientNameException
         * @throws InvalidClientPrivateHashException
         * @throws InvalidClientPublicHashException
         * @throws InvalidPlatformException
         * @throws InvalidVersionException
         */
        public function validate()
        {
            // Name validation
            if(gettype($this->Name) !== "string")
                throw new InvalidClientNameException("The client name must be a string");

            if(strlen($this->Name) == 0 || strlen($this->Name) > 32)
                throw new InvalidClientNameException("The client name cannot be empty or larger than 32 characters");

            if (preg_match(RegexPatterns::SpecialCharacters, $this->Name))
                throw new InvalidClientNameException("The client name contains invalid characters");


            if(gettype($this->Version) !== "string")
                throw new InvalidVersionException("The client version must be a string");

            if(strlen($this->Version) == 0 || strlen($this->Version) > 32)
                throw new InvalidVersionException("The version cannot be empty or larger than 32 characters");

            if (Validate::versionNumber($this->Version) == false)
                throw new InvalidClientNameException("The version is invalid");


            if(gettype($this->Platform) !== "string")
                throw new InvalidPlatformException("The platform name must be a string");

            if(strlen($this->Platform) == 0 || strlen($this->Platform) > 32)
                throw new InvalidPlatformException("The platform name cannot be empty or larger than 32 characters");

            if (preg_match(RegexPatterns::SpecialCharacters, $this->Platform))
                throw new InvalidPlatformException("The platform name contains invalid characters");


            if(gettype($this->PublicHash) !== "string")
                throw new InvalidClientPublicHashException("The client's public hash must be a string");

            if(strlen($this->PublicHash) !== 64)
                throw new InvalidClientPublicHashException("The client's public hash must be 64 characters in length");

            if(preg_match(RegexPatterns::Alphanumeric, $this->PublicHash))
                throw new InvalidClientPublicHashException("The client's public hash is not a valid hash");


            if(gettype($this->PrivateHash) !== "string")
                throw new InvalidClientPrivateHashException("The client's private hash must be a string");

            if(strlen($this->PrivateHash) !== 64)
                throw new InvalidClientPrivateHashException("The client's private hash must be 64 characters in length");

            if(preg_match(RegexPatterns::Alphanumeric, $this->PrivateHash))
                throw new InvalidClientPrivateHashException("The client's private hash is not a valid hash");

            if($this->PublicHash == $this->PrivateHash)
                throw new InvalidClientPrivateHashException("The client's private hash cannot be the same as the client's public hash");

        }
    }
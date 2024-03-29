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

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Types\Standard\PeerType;
    use SocialvoidLib\Objects\User;

    /**
     * Class StandardPeer
     * @package SocialvoidLib\Objects\Standard
     */
    class Peer
    {
        /**
         * The ID of the peer
         *
         * @var string
         */
        public $ID;

        /**
         * The account type of this peer
         *
         * @var string|PeerType
         */
        public $Type;

        /**
         * The username of the peer (Without the @)
         *
         * @var string
         */
        public $Username;

        /**
         * The name of the peer
         *
         * @var string
         */
        public $Name;

        /**
         * Array of flags associated with this peer
         *
         * @var string[]
         */
        public $Flags;

        /**
         * Returns an array representation of the peer
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "id" => $this->ID,
                "type" => $this->Type,
                "name" => $this->Name,
                "username" => $this->Username,
                "flags" => $this->Flags
            ];
        }

        /**
         * Constructs a peer object from a user object (Lib)
         *
         * @param User $user
         * @return Peer
         */
        public static function fromUser(User $user): Peer
        {
            $PeerObject = new Peer();

            $PeerObject->ID = $user->PublicID;
            $PeerObject->Type = PeerType::User; // TODO: This needs to be re-implemented
            $PeerObject->Username = $user->Username;
            $PeerObject->Flags = $user->Flags;

            if($user->Profile->LastName == null)
            {
                $PeerObject->Name = $user->Profile->FirstName;
            }
            else
            {
                $PeerObject->Name = $user->Profile->FirstName . ' ' . $user->Profile->LastName;
            }

            return $PeerObject;
        }

        /**
         * Constructs an object from an array representation
         *
         * @param array $data
         * @return Peer
         */
        public static function fromArray(array $data): Peer
        {
            $PeerObject = new Peer();

            if(isset($data["id"]))
                $PeerObject->ID = $data["id"];

            if(isset($data["type"]))
                $PeerObject->Type = $data["type"];

            if(isset($data["username"]))
                $PeerObject->Username = $data["username"];

            if(isset($data["name"]))
                $PeerObject->Name = $data['name'];

            if(isset($data["flags"]))
                $PeerObject->Flags = $data["flags"];

            return $PeerObject;
        }
    }
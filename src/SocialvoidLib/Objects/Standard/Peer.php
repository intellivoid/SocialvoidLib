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

    use SocialvoidLib\Objects\Standard\Peer\Name;
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
         * The username of the peer (Without the @)
         *
         * @var string
         */
        public $Username;

        /**
         * The network that this peer is from
         *
         * @var string
         */
        public $Network;

        /**
         * The full network address of the peer
         *
         * @var string
         */
        public $NetworkAddress;

        /**
         * The name of the peer
         *
         * @var Name
         */
        public $Name;

        /**
         * The Unix Timestamp for when this peer registered to the network
         *
         * @var int
         */
        public $CreatedTimestamp;

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
                "username" => $this->Username,
                "network" => $this->Network,
                "network_address" => $this->NetworkAddress,
                "name" => $this->Name->toArray(),
                "created_timestamp"=> $this->CreatedTimestamp,
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

            $PeerObject->Username = $user->Username;
            $PeerObject->ID = $user->PublicID;
            $PeerObject->Network = $user->Network;
            $PeerObject->Name = Name::fromProfile($user->Profile);
            $PeerObject->CreatedTimestamp = $user->CreatedTimestamp;
            $PeerObject->Flags = $user->Flags;
            $PeerObject->NetworkAddress = $user->Username . "@" . $user->Network;

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

            if(isset($data["username"]))
                $PeerObject->Username = $data["username"];

            if(isset($data["network"]))
                $PeerObject->Network = $data["network"];

            if(isset($data["name"]))
                $PeerObject->Name = Name::fromArray($data["name"]);

            if(isset($data["created_timestamp"]))
                $PeerObject->CreatedTimestamp = $data["created_timestamp"];

            if(isset($data["flags"]))
                $PeerObject->Flags = $data["flags"];

            $PeerObject->NetworkAddress = $PeerObject->Username . "@" . $PeerObject->Network;

            return $PeerObject;
        }
    }
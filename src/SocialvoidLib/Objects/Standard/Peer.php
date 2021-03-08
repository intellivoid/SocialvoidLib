<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Objects\Standard\Peer\Profile;
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
         * The profile data of the peer
         *
         * @var Profile
         */
        public $Profile;

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
                "profile" => $this->Profile->toArray(),
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
            $PeerObject->Profile = Profile::fromProfile($user->Profile);
            $PeerObject->CreatedTimestamp = $user->CreatedTimestamp;
            $PeerObject->Flags = $user->Flags;

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

            if(isset($data["profile"]))
                $PeerObject->Profile = Profile::fromArray($data["profile"]);

            if(isset($data["created_timestamp"]))
                $PeerObject->CreatedTimestamp = $data["created_timestamp"];

            if(isset($data["flags"]))
                $PeerObject->Flags = $data["flags"];

            return $PeerObject;
        }
    }
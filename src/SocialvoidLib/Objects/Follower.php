<?php

    /** @noinspection PhpMissingFieldTypeInspection */
    /** @noinspection PhpUnused */

    namespace SocialvoidLib\Objects;

    use SocialvoidLib\Abstracts\Flags\FollowerFlag;
    use SocialvoidLib\Abstracts\StatusStates\FollowerState;

    /**
     * Class Follower
     * @package SocialvoidLib\Objects
     */
    class Follower
    {
        /**
         * The Unique Internal Database ID
         *
         * @var int
         */
        public $ID;

        /**
         * A Unique Public ID for this follower record
         *
         * @var string
         */
        public $PublicID;

        /**
         * The user that initiated the following
         *
         * @var int
         */
        public $UserID;

        /**
         * The user that the source user is following
         *
         * @var int
         */
        public $TargetUserID;

        /**
         * The current state of the following status
         *
         * @var FollowerState
         */
        public $State;

        /**
         * Flags for this following state (Used for future improvements)
         *
         * @var FollowerFlag[]
         */
        public $Flags;

        /**
         * The Unix Timestamp for when this record was last updated
         *
         * @var int
         */
        public $LastUpdatedTimestamp;

        /**
         * The Unix Timestamp for when this record was created
         *
         * @var int
         */
        public $CreatedTimestamp;

        /**
         * Returns an array representation of this object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "id" => $this->ID,
                "public_id" => $this->PublicID,
                "user_id" => $this->UserID,
                "target_user_id" => $this->TargetUserID,
                "state" => $this->State,
                "flags" => $this->Flags,
                "last_updated_timestamp" => $this->LastUpdatedTimestamp,
                "created_timestamp" => $this->CreatedTimestamp
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return Follower
         */
        public static function fromArray(array $data): Follower
        {
            $FollowerObject = new Follower();

            if(isset($data["id"]))
            {
                if($data["id"] !== null)
                    $FollowerObject->ID = (int)$data["id"];
            }

            if(isset($data["public_id"]))
                $FollowerObject->PublicID = $data["public_id"];

            if(isset($data["user_id"]))
            {
                if($data["user_id"] !== null)
                    $FollowerObject->UserID = (int)$data["user_id"];
            }

            if(isset($data["target_user_id"]))
            {
                if($data["target_user_id"] !== null)
                    $FollowerObject->TargetUserID = (int)$data["target_user_id"];
            }

            if(isset($data["state"]))
                $FollowerObject->State = $data["state"];

            if(isset($data["flags"]))
                $FollowerObject->Flags = $data["flags"];

            if(isset($data["last_updated_timestamp"]))
            {
                if($data["last_updated_timestamp"] !== null)
                    $FollowerObject->LastUpdatedTimestamp = (int)$data["last_updated_timestamp"];
            }

            if(isset($data["created_timestamp"]))
            {
                if(isset($data["created_timestamp"]) !== null)
                    $FollowerObject->CreatedTimestamp = $data["created_timestamp"];
            }

            return $FollowerObject;
        }
    }
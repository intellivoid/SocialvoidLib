<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects;

    /**
     * Class FollowerData
     * @package SocialvoidLib\Objects
     */
    class FollowerData
    {
        /**
         * The Unique Internal Database ID
         *
         * @var int
         */
        public $ID;

        /**
         * The user ID that this following data is associated with
         *
         * @var int
         */
        public $UserID;

        /**
         * The total amount of followers that are following this user
         *
         * @var int
         */
        public $Followers;

        /**
         * The array of user IDs that are currently following this user
         *
         * @var int[]
         */
        public $FollowersIDs;

        /**
         * The total amount of users that this user is following
         *
         * @var int
         */
        public $Following;

        /**
         * The array of user IDs that this user is following
         *
         * @var int[]
         */
        public $FollowingIDs;

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
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "id" => $this->ID,
                "user_id" => $this->UserID,
                "followers" => $this->Followers,
                "followers_ids" => $this->FollowersIDs,
                "following" => $this->Following,
                "following_ids" => $this->FollowingIDs,
                "last_updated_timestamp" => $this->LastUpdatedTimestamp,
                "created_timestamp" => $this->CreatedTimestamp
            ];
        }

        /**
         * Constructs an object from an array representation
         *
         * @param array $data
         * @return FollowerData
         */
        public static function fromArray(array $data): FollowerData
        {
            $FollowerDataObject = new FollowerData();

            if(isset($data["id"]))
            {
                if($data["id"] !== null)
                    $FollowerDataObject->ID = (int)$data["id"];
            }

            if(isset($data["user_id"]))
            {
                if($data["user_id"] !== null)
                    $FollowerDataObject->UserID = (int)$data["user_id"];
            }

            if(isset($data["followers"]))
            {
                if($data["followers"] !== null)
                    $FollowerDataObject->Followers = (int)$data["followers"];
            }

            if(isset($ata["followers_ids"]))
                $FollowerDataObject->FollowingIDs = $data["followers_ids"];

            if(isset($data["following"]))
            {
                if($data["following"] !== null)
                    $FollowerDataObject->Following = (int)$data["following"];
            }

            if(isset($ata["following_ids"]))
                $FollowerDataObject->FollowingIDs = $data["following_ids"];

            if(isset($data["last_updated_timestamp"]))
            {
                if($data["last_updated_timestamp"] !== null)
                    $FollowerDataObject->LastUpdatedTimestamp = (int)$data["last_updated_timestamp"];
            }

            if(isset($data["created_timestamp"]))
            {
                if($data["created_timestamp"] !== null)
                    $FollowerDataObject->CreatedTimestamp = (int)$data["created_timestamp"];
            }

            return $FollowerDataObject;
        }
    }
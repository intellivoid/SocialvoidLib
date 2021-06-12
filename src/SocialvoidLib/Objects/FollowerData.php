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
         * @deprecated No longer used, use `user_id` ($UserID) for a unique index and as a primary key
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
         * FollowerData constructor.
         */
        public function __construct()
        {
            $this->FollowingIDs = [];
            $this->FollowersIDs = [];
        }

        /**
         * Adds a new follower to the equation
         *
         * @param int $user_id
         */
        public function addFollower(int $user_id): void
        {
            if($this->FollowersIDs == null)
                $this->FollowersIDs = [];

            if(in_array($user_id, $this->FollowersIDs))
                return;

            $this->FollowersIDs[] = $user_id;
            $this->Followers = count($this->FollowersIDs);
        }

        /**
         * Removes a follower from the equation
         *
         * @param int $user_id
         */
        public function removeFollower(int $user_id): void
        {
            if($this->FollowersIDs == null)
                $this->FollowersIDs = [];

            if(in_array($user_id, $this->FollowersIDs) == false)
                return;

            $this->FollowersIDs = array_diff($this->FollowersIDs, [$user_id]);
            $this->Followers = count($this->FollowersIDs);
        }

        /**
         * Adds a new following to the equation
         *
         * @param int $user_id
         */
        public function addFollowing(int $user_id): void
        {
            if($this->FollowingIDs == null)
                $this->FollowingIDs = [];

            if(in_array($user_id, $this->FollowingIDs))
                return;

            $this->FollowingIDs[] = $user_id;
            $this->Following = count($this->FollowingIDs);
        }

        /**
         * Removes a following from the equation
         *
         * @param int $user_id
         */
        public function removeFollowing(int $user_id): void
        {
            if($this->FollowingIDs == null)
                $this->FollowingIDs = [];

            if(in_array($user_id, $this->FollowingIDs) == false)
                return;

            $this->FollowingIDs = array_diff($this->FollowingIDs, [$user_id]);
            $this->Following = count($this->FollowingIDs);
        }

        /**
         * Determines if both a user are following each other.
         *
         * @param int $user_id
         * @return bool
         */
        public function isMutual(int $user_id): bool
        {
            if($this->FollowersIDs == null)
                $this->FollowersIDs = [];
            if($this->FollowingIDs == null)
                $this->FollowingIDs = [];

            if(in_array($user_id, $this->FollowingIDs) && in_array($user_id, $this->FollowersIDs))
            {
                return true;
            }

            return false;
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                //"id" => $this->ID,
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

            /**
             * if(isset($data["id"]))
             * {
             *    if($data["id"] !== null)
             *        $FollowerDataObject->ID = (int)$data["id"];
             * }
             */

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

            if(isset($data["followers_ids"]))
                $FollowerDataObject->FollowersIDs = $data["followers_ids"];

            if(isset($data["following"]))
            {
                if($data["following"] !== null)
                    $FollowerDataObject->Following = (int)$data["following"];
            }

            if(isset($data["following_ids"]))
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
<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\User;

    /**
     * Class UserProperties
     * @package SocialvoidLib\Objects\User
     */
    class UserProperties
    {
        /**
         * The current amount of users that this user is following
         *
         * @var int
         */
        public $FollowingCount;

        /**
         * The Unix Timestamp for when the following count was last updated
         *
         * @var int
         */
        public $FollowingCountLastUpdatedTimestamp;

        /**
         * The current amount of followers following this user
         *
         * @var int
         */
        public $FollowersCount;

        /**
         * The Unix Timestamp for when the followers count was last updated
         *
         * @var int
         */
        public $FollowersCountLastUpdatedTimestamp;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "followers_count" => $this->FollowersCount,
                "followers_count_last_updated" => $this->FollowersCountLastUpdatedTimestamp,
                "following_count" => $this->FollowingCount,
                "following_count_last_updated" => $this->FollowingCountLastUpdatedTimestamp
            ];
        }

        /**
         * Constructs the user properties object from an array representation
         *
         * @param array $data
         * @return UserProperties
         */
        public static function fromArray(array $data): UserProperties
        {
            $UserPropertiesObject = new UserProperties();

            if(isset($data["followers_count"]))
                $UserPropertiesObject->FollowersCount = $data["followers_count"];

            if(isset($data["followers_count_last_updated"]))
                $UserPropertiesObject->FollowersCountLastUpdatedTimestamp = $data["followers_count_last_updated"];

            if(isset($data["following_count"]))
                $UserPropertiesObject->FollowingCount = $data["following_count"];

            if(isset($data["following_count_last_updated"]))
                $UserPropertiesObject->FollowingCountLastUpdatedTimestamp = $data["following_count_last_updated"];

            return $UserPropertiesObject;
        }
    }
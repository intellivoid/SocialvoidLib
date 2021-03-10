<?php

    /** @noinspection PhpMissingFieldTypeInspection */
    /** @noinspection PhpUnused */

    namespace SocialvoidLib\Objects;


    /**
     * Class Repost
     * @package SocialvoidLib\Objects
     */
    class RepostRecord
    {
        /**
         * The Unique Internal Database ID for this record
         *
         * @var double
         */
        public $ID;

        /**
         * The user ID that liked the post
         *
         * @var int
         */
        public $UserID;

        /**
         * The Post ID that this record is associated with
         *
         * @var int
         */
        public $PostID;

        /**
         * Indicates if the user currently reposted this post
         *
         * @var bool
         */
        public $Reposted;

        /**
         * The Unix Timestamp for when this record was created
         *
         * @var int
         */
        public $CreatedTimestamp;

        /**
         * The Unix Timestamp for when this record was last updated
         *
         * @var int
         */
        public $LastUpdatedTimestamp;

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
                "post_id" => $this->PostID,
                "reposted" => $this->Reposted,
                "last_updated_timestamp" => $this->LastUpdatedTimestamp,
                "created_timestamp" => $this->CreatedTimestamp,
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return RepostRecord
         */
        public static function fromArray(array $data): RepostRecord
        {
            $RepostRecordObject = new RepostRecord();

            if(isset($data["id"]))
                $RepostRecordObject->ID = (double)$data["id"];

            if(isset($data["user_id"]))
                $RepostRecordObject->UserID = (int)$data["user_id"];

            if(isset($data["post_id"]))
                $RepostRecordObject->PostID = (int)$data["post_id"];

            if(isset($data["reposted"]))
                $RepostRecordObject->Reposted = (bool)$data["reposted"];

            if(isset($data["last_updated_timestamp"]))
                $RepostRecordObject->LastUpdatedTimestamp = (int)$data["last_updated_timestamp"];

            if(isset($data["created_timestamp"]))
                $RepostRecordObject->CreatedTimestamp = (int)$data["created_timestamp"];

            return $RepostRecordObject;
        }
    }
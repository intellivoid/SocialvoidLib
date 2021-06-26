<?php

    /** @noinspection PhpMissingFieldTypeInspection */
    /** @noinspection PhpUnused */

    namespace SocialvoidLib\Objects;


    /**
     * Class LikeRecord
     * @package SocialvoidLib\Objects
     */
    class LikeRecord
    {
        /**
         * The Unique Internal Database ID for this record
         *
         * @var string
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
         * @var string
         */
        public $PostID;

        /**
         * Indicates if the user currently likes this post
         *
         * @var bool
         */
        public $Liked;

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
                "liked" => $this->Liked,
                "last_updated_timestamp" => $this->LastUpdatedTimestamp,
                "created_timestamp" => $this->CreatedTimestamp,
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return LikeRecord
         * @noinspection DuplicatedCode
         */
        public static function fromArray(array $data): LikeRecord
        {
            $LikeRecordObject = new LikeRecord();

            if(isset($data["id"]))
                $LikeRecordObject->ID = $data["id"];

            if(isset($data["user_id"]))
                $LikeRecordObject->UserID = (int)$data["user_id"];

            if(isset($data["post_id"]))
                $LikeRecordObject->PostID = $data["post_id"];

            if(isset($data["liked"]))
                $LikeRecordObject->Liked = (bool)$data["liked"];

            if(isset($data["last_updated_timestamp"]))
                $LikeRecordObject->LastUpdatedTimestamp = (int)$data["last_updated_timestamp"];

            if(isset($data["created_timestamp"]))
                $LikeRecordObject->CreatedTimestamp = (int)$data["created_timestamp"];

            return $LikeRecordObject;
        }
    }
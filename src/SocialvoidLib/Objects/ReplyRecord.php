<?php

    /** @noinspection PhpMissingFieldTypeInspection */
    /** @noinspection PhpUnused */

    namespace SocialvoidLib\Objects;


    /**
     * Class ReplyRecord
     * @package SocialvoidLib\Objects
     */
    class ReplyRecord
    {
        /**
         * The Unique Internal Database ID for this record
         *
         * @var double|int
         */
        public $ID;

        /**
         * The user ID that liked the post
         *
         * @var int
         */
        public $UserID;

        /**
         * The Target Post ID that this record is associated with
         *
         * @var int
         */
        public $PostID;

        /**
         * The post ID of the reply
         *
         * @var int
         */
        public $ReplyPostID;

        /**
         * Indicates if the user currently replies this post
         *
         * @var bool
         */
        public $Replied;

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
                "reply_post_id" => $this->ReplyPostID,
                "quoted" => $this->Replied,
                "last_updated_timestamp" => $this->LastUpdatedTimestamp,
                "created_timestamp" => $this->CreatedTimestamp,
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return ReplyRecord
         * @noinspection DuplicatedCode
         */
        public static function fromArray(array $data): ReplyRecord
        {
            $ReplyRecordObject = new ReplyRecord();

            if(isset($data["id"]))
                $ReplyRecordObject->ID = (double)$data["id"];

            if(isset($data["user_id"]))
                $ReplyRecordObject->UserID = (int)$data["user_id"];

            if(isset($data["post_id"]))
                $ReplyRecordObject->PostID = (int)$data["post_id"];

            if(isset($data["reply_post_id"]))
                $ReplyRecordObject->ReplyPostID = (int)$data["reply_post_id"];

            if(isset($data["replied"]))
                $ReplyRecordObject->Replied = (bool)$data["replied"];

            if(isset($data["last_updated_timestamp"]))
                $ReplyRecordObject->LastUpdatedTimestamp = (int)$data["last_updated_timestamp"];

            if(isset($data["created_timestamp"]))
                $ReplyRecordObject->CreatedTimestamp = (int)$data["created_timestamp"];

            return $ReplyRecordObject;
        }
    }
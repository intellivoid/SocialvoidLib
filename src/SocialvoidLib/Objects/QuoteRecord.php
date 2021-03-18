<?php

    /** @noinspection PhpMissingFieldTypeInspection */
    /** @noinspection PhpUnused */

    namespace SocialvoidLib\Objects;


    /**
     * Class QuoteRecord
     * @package SocialvoidLib\Objects
     */
    class QuoteRecord
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
         * The Post ID that this record is associated with
         *
         * @var int
         */
        public $PostID;

        /**
         * The original post ID that the PostID is quoting
         *
         * @var int
         */
        public $OriginalPostID;

        /**
         * Indicates if the user currently quotes this post
         *
         * @var bool
         */
        public $Quoted;

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
                "original_post_id" => $this->OriginalPostID,
                "quoted" => $this->Quoted,
                "last_updated_timestamp" => $this->LastUpdatedTimestamp,
                "created_timestamp" => $this->CreatedTimestamp,
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return QuoteRecord
         * @noinspection DuplicatedCode
         */
        public static function fromArray(array $data): QuoteRecord
        {
            $QuoteRecordObject = new QuoteRecord();

            if(isset($data["id"]))
                $QuoteRecordObject->ID = (double)$data["id"];

            if(isset($data["user_id"]))
                $QuoteRecordObject->UserID = (int)$data["user_id"];

            if(isset($data["post_id"]))
                $QuoteRecordObject->PostID = (int)$data["post_id"];

            if(isset($data["original_post_id"]))
                $QuoteRecordObject->OriginalPostID = (int)$data["original_post_id"];

            if(isset($data["quoted"]))
                $QuoteRecordObject->Quoted = (bool)$data["quoted"];

            if(isset($data["last_updated_timestamp"]))
                $QuoteRecordObject->LastUpdatedTimestamp = (int)$data["last_updated_timestamp"];

            if(isset($data["created_timestamp"]))
                $QuoteRecordObject->CreatedTimestamp = (int)$data["created_timestamp"];

            return $QuoteRecordObject;
        }
    }
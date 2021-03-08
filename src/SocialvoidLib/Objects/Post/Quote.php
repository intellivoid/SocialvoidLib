<?php

    /** @noinspection PhpMissingFieldTypeInspection */
    /** @noinspection PhpUnused */

    namespace SocialvoidLib\Objects\Post;

    /**
     * Class Quote
     * @package SocialvoidLib\Objects\Post
     */
    class Quote
    {
        /**
         * The original post ID that is quoted in the post
         *
         * @var int
         */
        public $OriginalPostID;

        /**
         * The original user ID of the quoted post
         *
         * @var int
         */
        public $OriginalUserID;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "original_post_id" => $this->OriginalPostID,
                "original_user_id" => $this->OriginalUserID
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return Quote
         */
        public static function fromArray(array $data): Quote
        {
            $QuoteObject = new Quote();

            if(isset($data["original_post_id"]))
                $QuoteObject->OriginalPostID = $data["original_post_id"];

            if(isset($data["original_user_id"]))
                $QuoteObject->OriginalPostID = $data["original_user_id"];

            return $QuoteObject;
        }
    }
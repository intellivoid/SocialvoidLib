<?php

    /** @noinspection PhpMissingFieldTypeInspection */
    /** @noinspection PhpUnused */

    namespace SocialvoidLib\Objects\Post;

    /**
     * Class Reply
     * @package SocialvoidLib\Objects\Post
     */
    class Reply
    {
        /**
         * The post ID that this reply is targeted to
         *
         * @var int|null
         */
        public $ReplyToPostID;

        /**
         * The User ID that this reply is targeted to
         *
         * @var int|null
         */
        public $ReplyToUserID;

        /**
         * Returns an array representation of this object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "reply_to_post_id" => $this->ReplyToPostID,
                "reply_to_user_id" => $this->ReplyToUserID
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return Reply
         */
        public static function fromArray(array $data): Reply
        {
            $ReplyObject = new Reply();

            if(isset($data["reply_to_post_id"]))
                $ReplyObject = $data["reply_to_post_id"];


            if(isset($data["reply_to_user_id"]))
                $ReplyObject = $data["reply_to_user_id"];

            return $ReplyObject;
        }
    }
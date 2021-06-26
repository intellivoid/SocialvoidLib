<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

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
         * @var string|null
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
                $ReplyObject->ReplyToPostID = $data["reply_to_post_id"];


            if(isset($data["reply_to_user_id"]))
                $ReplyObject->ReplyToUserID = $data["reply_to_user_id"];

            return $ReplyObject;
        }
    }
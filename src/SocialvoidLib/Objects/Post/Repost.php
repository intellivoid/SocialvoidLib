<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

namespace SocialvoidLib\Objects\Post;

    /**
     * Class Repost
     * @package SocialvoidLib\Objects\Post
     */
    class Repost
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
         * @return Repost
         */
        public static function fromArray(array $data): Repost
        {
            $RepostObject = new Repost();

            if(isset($data["original_post_id"]))
                $RepostObject->OriginalPostID = $data["original_post_id"];

            if(isset($data["original_user_id"]))
                $RepostObject->OriginalPostID = $data["original_user_id"];

            return $RepostObject;
        }
    }
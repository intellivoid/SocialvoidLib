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

    namespace SocialvoidLib\Objects\Post;

    /**
     * Class Entities
     * @package SocialvoidLib\Objects\Post
     */
    class Entities
    {
        /**
         * Array of hashtags found in this post
         *
         * @var string[]
         */
        public $Hashtags;

        /**
         * Array of URLs found in this post
         *
         * @var string[]
         */
        public $Urls;

        /**
         * Array of user mentions on the post
         *
         * @var string[]
         */
        public $UserMentions;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "hashtags" => $this->Hashtags,
                "urls" => $this->Urls,
                "user_mentions" => $this->UserMentions
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return Entities
         */
        public static function fromArray(array $data): Entities
        {
            $EntitiesObject = new Entities();

            if(isset($data["hashtags"]))
                $EntitiesObject = $data["hashtags"];

            if(isset($data["urls"]))
                $EntitiesObject = $data["urls"];

            if(isset($data["user_mentions"]))
                $EntitiesObject = $data["user_mentions"];

            return $EntitiesObject;
        }
    }
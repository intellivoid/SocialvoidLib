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

    namespace SocialvoidLib\Objects;

    use SocialvoidLib\Objects\Post\Entities;
    use SocialvoidLib\Objects\Post\MediaContent;
    use SocialvoidLib\Objects\Post\Properties;
    use SocialvoidLib\Objects\Post\Quote;
    use SocialvoidLib\Objects\Post\Reply;
    use SocialvoidLib\Objects\Post\Repost;

    /**
     * Class Post
     * @package SocialvoidLib\Objects
     */
    class Post
    {
        /**
         * The Unique Internal Database ID for this post
         *
         * @deprecated The use of incremental IDs is harmful for the future of the earth.
         * @see https://github.com/intellivoid/SocialvoidLib/issues/1
         * @var int
         */
        public $ID;

        /**
         * The Public ID of this post
         *
         * @var string
         */
        public $PublicID;

        /**
         * The actual UTF-8 text of the status update
         *
         * @var string|null
         */
        public $Text;

        /**
         * The device used to post this
         *
         * @var string
         */
        public $Source;

        /**
         * The properties associated with this post
         *
         * @var Properties
         */
        public $Properties;

        /**
         * The session ID used to make this post
         *
         * @var string|null
         */
        public $SessionID;

        /**
         * The original author of this post
         *
         * @var int
         */
        public $PosterUserID;

        /**
         * Information about this post's reply status
         *
         * @var Reply|null
         */
        public $Reply;

        /**
         * Information about the quote this post is mentioning
         *
         * @var Quote|null
         */
        public $Quote;

        /**
         * Information about this post being a repost
         *
         * @var Repost|null
         */
        public $Repost;

        /**
         * Flags associated with this post
         *
         * @var array
         */
        public $Flags;

        /**
         * The level of this post's priority
         *
         * @var string
         */
        public $PriorityLevel;

        /**
         * @var Entities
         */
        public $Entities;

        /**
         * Array of User IDs that liked this post
         *
         * @var int[]
         */
        public $Likes;

        /**
         * Array of user IDs that reposted this post
         *
         * @var int[]
         */
        public $Reposts;

        /**
         * Array if Post IDs that quoted this post
         *
         * @var int[]
         */
        public $Quotes;

        /**
         * Array of Post IDs that replied to this post
         *
         * @var int[]
         */
        public $Replies;

        /**
         * The media content associated with this post
         *
         * @var MediaContent[]
         */
        public $MediaContent;


        /**
         * The Unix Timestamp for when this record was last updated
         *
         * @var int
         */
        public $LastUpdatedTimestamp;

        /**
         * The Unix Timestamp of when this was posted
         *
         * @var int
         */
        public $CreatedTimestamp;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            $media_content_results = null;

            if($this->MediaContent !== null)
            {
                $media_content_results = [];
                foreach($this->MediaContent as $mediaContent)
                    $media_content_results[] = $mediaContent->toArray();
            }
            
            return [
                //"id" => ($this->ID == null ? null : (int)$this->ID), https://github.com/intellivoid/SocialvoidLib/issues/1
                "public_id" => $this->PublicID,
                "text" => $this->Text,
                "source" => $this->Source,
                "properties" => ($this->Properties == null ? null : $this->Properties->toArray()),
                "session_id" => ($this->SessionID == null ? null : $this->SessionID),
                "poster_user_id" => $this->PosterUserID,
                "reply" => ($this->Reply == null ? null : $this->Reply->toArray()),
                "quote" => ($this->Quote == null ? null : $this->Quote->toArray()),
                "repost" => ($this->Repost == null ? null : $this->Repost->toArray()),
                "flags" => ($this->Flags == null ? [] : $this->Flags),
                "priority_level" => ($this->Flags == null ? [] : $this->Flags),
                "entities" => ($this->Entities == null ? null : $this->Entities->toArray()),
                "likes" => ($this->Likes == null ? [] : $this->Likes),
                "reposts" => ($this->Reposts == null ? [] : $this->Reposts),
                "quotes" => ($this->Quotes == null ? [] : $this->Quotes),
                "replies" => ($this->Replies == null ? []: $this->Replies),
                "media_content" => $media_content_results,
                "last_updated_timestamp" => ($this->LastUpdatedTimestamp == null ? null : $this->LastUpdatedTimestamp),
                "created_timestamp" => ($this->CreatedTimestamp == null ? null : $this->CreatedTimestamp)
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return Post
         * @noinspection DuplicatedCode
         */
        public static function fromArray(array $data): Post
        {
            $PostObject = new Post();

            // https://github.com/intellivoid/SocialvoidLib/issues/1
            //if(isset($data["id"]))
            //    $PostObject->ID = ($data["id"] == null ? null : (int)$data["id"]);

            if(isset($data["public_id"]))
                $PostObject->PublicID = $data["public_id"];

            if(isset($data["text"]))
                $PostObject->Text = $data["text"];

            if(isset($data["source"]))
                $PostObject->Source = $data["source"];

            if(isset($data["properties"]))
                $PostObject->Properties = ($data["properties"] == null ? new Properties() : Properties::fromArray($data["properties"]));

            if(isset($data["session_id"]))
                $PostObject->SessionID = ($data["session_id"] == null ? null : $data["session_id"]);

            if(isset($data["poster_user_id"]))
                $PostObject->PosterUserID = ($data["poster_user_id"] == null ? null : (int)$data["poster_user_id"]);

            if(isset($data["reply"]))
                $PostObject->Reply = Reply::fromArray($data["reply"]);

            if(isset($data["quote"]))
                $PostObject->Quote = Quote::fromArray($data["quote"]);

            if(isset($data["repost"]))
                $PostObject->Repost = Repost::fromArray($data["repost"]);

            if(isset($data["flags"]))
                $PostObject->Flags = ($data["flags"] == null ? [] : $data["flags"]);

            if(isset($data["priority_level"]))
                $PostObject->PriorityLevel = $data["priority_level"];

            if(isset($data["entities"]))
                $PostObject->Entities = ($data["entities"] !== null ? Entities::fromArray($data["entities"]) : null);

            if(isset($data["likes"]))
                $PostObject->Likes = ($data["likes"] == null ? [] : $data["likes"]);

            if(isset($data["reposts"]))
                $PostObject->Reposts = ($data["reposts"] == null ? [] : $data["reposts"]);

            if(isset($data["quotes"]))
                $PostObject->Quotes = ($data["quotes"] == null ? [] : $data["quotes"]);

            if(isset($data["replies"]))
                $PostObject->Replies = ($data["replies"] == null ? [] : $data["replies"]);

            if(isset($data["media_content"]))
            {
                $PostObject->MediaContent = [];
                foreach($data["media_content"] as $datum)
                    $PostObject->MediaContent[] = MediaContent::fromArray($datum);
            }

            if(isset($data["last_updated_timestamp"]))
                $PostObject->LastUpdatedTimestamp = ($data["last_updated_timestamp"] == null ? null : (int)$data["last_updated_timestamp"]);

            if(isset($data["created_timestamp"]))
                $PostObject->CreatedTimestamp = ($data["created_timestamp"] == null ? null : (int)$data["created_timestamp"]);

            return $PostObject;
        }

        /**
         * Returns an alternative representation of an objects array
         *
         * @return array
         */
        public function toArrayAlternative(): array
        {
            $media_content_results = null;

            if($this->MediaContent !== null)
            {
                $media_content_results = [];
                foreach($this->MediaContent as $mediaContent)
                    $media_content_results[] = $mediaContent->toArray();
            }

            return [
                //"id" => ($this->ID == null ? null : (int)$this->ID),
                "public_id" => $this->PublicID,
                "text" => $this->Text,
                "source" => $this->Source,
                "properties" => ($this->Properties == null ? null : $this->Properties->toArray()),
                "session_id" => ($this->SessionID == null ? null : $this->SessionID),
                "poster_user_id" => $this->PosterUserID,
                "reply_to_post_id" => ($this->Reply == null ? null : $this->Reply->ReplyToPostID),
                "reply_to_user_id" => ($this->Reply == null ? null : $this->Reply->ReplyToUserID),
                "quote_original_post_id" => ($this->Quote == null ? null : $this->Quote->OriginalPostID),
                "quote_original_user_id" => ($this->Quote == null ? null : $this->Quote->OriginalUserID),
                "repost_original_post_id" => ($this->Repost == null ? null : $this->Repost->OriginalPostID),
                "repost_original_user_id" => ($this->Repost == null ? null : $this->Repost->OriginalUserID),
                "flags" => ($this->Flags == null ? [] : $this->Flags),
                "priority_level" => ($this->Flags == null ? [] : $this->Flags),
                "entities" => ($this->Entities == null ? null : $this->Entities->toArray()),
                "likes" => ($this->Likes == null ? [] : $this->Likes),
                "reposts" => ($this->Reposts == null ? [] : $this->Reposts),
                "quotes" => ($this->Quotes == null ? [] : $this->Quotes),
                "replies" => ($this->Replies == null ? [] : $this->Replies),
                "media_content" => $media_content_results,
                "last_updated_timestamp" => ($this->LastUpdatedTimestamp == null ? null : $this->LastUpdatedTimestamp),
                "created_timestamp" => ($this->CreatedTimestamp == null ? null : $this->CreatedTimestamp)
            ];
        }

        /** @noinspection DuplicatedCode */
        public static function fromAlternativeArray(array $data): Post
        {
            $PostObject = new Post();

            //if(isset($data["id"]))
            //    $PostObject->ID = ($data["id"] == null ? null : (int)$data["id"]);

            if(isset($data["public_id"]))
                $PostObject->PublicID = $data["public_id"];

            if(isset($data["text"]))
                $PostObject->Text = $data["text"];

            if(isset($data["source"]))
                $PostObject->Source = $data["source"];

            if(isset($data["properties"]))
                $PostObject->Properties = ($data["properties"] == null ? new Properties() : Properties::fromArray($data["properties"]));

            if(isset($data["session_id"]))
                $PostObject->SessionID = ($data["session_id"] == null ? null : $data["session_id"]);

            if(isset($data["poster_user_id"]))
                $PostObject->PosterUserID = ($data["poster_user_id"] == null ? null : (int)$data["poster_user_id"]);

            if(isset($data["reply_to_post_id"]))
            {
                if($PostObject->Reply == null)
                    $PostObject->Reply = new Reply();
                $PostObject->Reply->ReplyToPostID = $data["reply_to_post_id"];
            }

            if(isset($data["reply_to_user_id"]))
            {
                if($PostObject->Reply == null)
                    $PostObject->Reply = new Reply();
                $PostObject->Reply->ReplyToUserID = (int)$data["reply_to_user_id"];
            }

            if(isset($data["quote_original_post_id"]))
            {
                if($PostObject->Quote == null)
                    $PostObject->Quote = new Quote();
                $PostObject->Quote->OriginalPostID = $data["quote_original_post_id"];
            }

            if(isset($data["quote_original_user_id"]))
            {
                if($PostObject->Quote == null)
                    $PostObject->Quote = new Quote();
                $PostObject->Quote->OriginalUserID = (int)$data["quote_original_user_id"];
            }

            if(isset($data["repost_original_post_id"]))
            {
                if($PostObject->Repost == null)
                    $PostObject->Repost = new Repost();
                $PostObject->Repost->OriginalPostID = $data["repost_original_post_id"];
            }

            if(isset($data["repost_original_user_id"]))
            {
                if($PostObject->Repost == null)
                    $PostObject->Repost = new Quote();
                $PostObject->Repost->OriginalUserID = (int)$data["repost_original_user_id"];
            }

            if(isset($data["flags"]))
                $PostObject->Flags = ($data["flags"] !== null ? [] : $data["flags"]);

            if(isset($data["priority_level"]))
                $PostObject->PriorityLevel = $data["priority_level"];

            if(isset($data["entities"]))
                $PostObject->Entities = ($data["entities"] !== null ? Entities::fromArray($data["entities"]) : null);

            if(isset($data["likes"]))
                $PostObject->Likes = ($data["likes"] == null ? [] : $data["likes"]);

            if(isset($data["reposts"]))
                $PostObject->Reposts = ($data["reposts"] == null ? [] : $data["reposts"]);

            if(isset($data["quotes"]))
                $PostObject->Quotes = ($data["quotes"] == null ? [] : $data["quotes"]);

            if(isset($data["replies"]))
                $PostObject->Replies = ($data["replies"] == null ? [] : $data["replies"]);

            if(isset($data["media_content"]))
            {
                $PostObject->MediaContent = [];
                foreach($data["media_content"] as $datum)
                    $PostObject->MediaContent[] = MediaContent::fromArray($datum);
            }

            if(isset($data["last_updated_timestamp"]))
                $PostObject->LastUpdatedTimestamp = ($data["last_updated_timestamp"] == null ? null : (int)$data["last_updated_timestamp"]);

            if(isset($data["created_timestamp"]))
                $PostObject->CreatedTimestamp = ($data["created_timestamp"] == null ? null : (int)$data["created_timestamp"]);

            return $PostObject;
        }
    }
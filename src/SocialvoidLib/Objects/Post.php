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

    use SocialvoidLib\Objects\Post\Properties;
    use SocialvoidLib\Objects\Post\Quote;
    use SocialvoidLib\Objects\Post\Reply;
    use SocialvoidLib\Objects\Post\Repost;
    use SocialvoidLib\Objects\Standard\TextEntity;

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
         * The original post that this thread is for
         *
         * @var string|null
         */
        public $OriginalPostThreadID;

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
         * @var TextEntity[]
         */
        public $TextEntities;

        /**
         * The amount of likes this post has
         * 
         * @var int
         */
        public $LikeCount;

        /**
         * The amount of reposts this post has
         * 
         * @var int
         */
        public $RepostCount;

        /**
         * The amount of quotes this post has
         * 
         * @var int
         */
        public $QuoteCount;

        /**
         * The amount of replies this post has
         * 
         * @var int
         */
        public $ReplyCount;

        /**
         * An array of Document IDs attached to this post
         *
         * @var string[]
         */
        public $Attachments;

        /**
         * The Unix Timestamp for when this records counts was last updated
         *
         * @var int
         */
        public $CountLastUpdatedTimestamp;

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
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function toArray(): array
        {
            $text_entities = [];

            if($this->TextEntities !== null)
            {
                foreach($this->TextEntities as $textEntity)
                    $text_entities[] = $textEntity->toArray();
            }

            return [
                //'id' => ($this->ID == null ? null : (int)$this->ID), https://github.com/intellivoid/SocialvoidLib/issues/1
                'public_id' => $this->PublicID,
                'text' => $this->Text,
                'source' => $this->Source,
                'properties' => ($this->Properties == null ? null : $this->Properties->toArray()),
                'session_id' => ($this->SessionID == null ? null : $this->SessionID),
                'poster_user_id' => $this->PosterUserID,
                'reply' => ($this->Reply == null ? null : $this->Reply->toArray()),
                'quote' => ($this->Quote == null ? null : $this->Quote->toArray()),
                'repost' => ($this->Repost == null ? null : $this->Repost->toArray()),
                'original_thread_post_id' => $this->OriginalPostThreadID,
                'flags' => ($this->Flags == null ? [] : $this->Flags),
                'priority_level' => ($this->Flags == null ? [] : $this->Flags),
                'text_entities' => $text_entities,
                'like_count' => ($this->LikeCount == null ? 0 : (int)$this->LikeCount),
                'repost_count' => ($this->RepostCount == null ? 0 : (int)$this->RepostCount),
                'quote_count' => ($this->QuoteCount == null ? 0 : (int)$this->QuoteCount),
                'reply_count' => ($this->ReplyCount == null ? 0 : (int)$this->ReplyCount),
                'attachments' => ($this->Attachments == null ? [] : $this->Attachments),
                'count_last_updated_timestamp' => ($this->CountLastUpdatedTimestamp == null ? 0 : $this->CountLastUpdatedTimestamp),
                'last_updated_timestamp' => ($this->LastUpdatedTimestamp == null ? null : $this->LastUpdatedTimestamp),
                'created_timestamp' => ($this->CreatedTimestamp == null ? null : $this->CreatedTimestamp)
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
            $PostObject->TextEntities = [];
            $PostObject->Attachments = [];

            // https://github.com/intellivoid/SocialvoidLib/issues/1
            //if(isset($data['id']))
            //    $PostObject->ID = ($data['id'] == null ? null : (int)$data['id']);

            if(isset($data['public_id']))
                $PostObject->PublicID = $data['public_id'];

            if(isset($data['text']))
                $PostObject->Text = $data['text'];

            if(isset($data['source']))
                $PostObject->Source = $data['source'];

            if(isset($data['properties']))
                $PostObject->Properties = ($data['properties'] == null ? new Properties() : Properties::fromArray($data['properties']));

            if(isset($data['session_id']))
                $PostObject->SessionID = ($data['session_id'] == null ? null : $data['session_id']);

            if(isset($data['poster_user_id']))
                $PostObject->PosterUserID = ($data['poster_user_id'] == null ? null : (int)$data['poster_user_id']);

            if(isset($data['reply']))
                $PostObject->Reply = Reply::fromArray($data['reply']);

            if(isset($data['quote']))
                $PostObject->Quote = Quote::fromArray($data['quote']);

            if(isset($data['repost']))
                $PostObject->Repost = Repost::fromArray($data['repost']);

            if(isset($data['flags']))
                $PostObject->Flags = ($data['flags'] == null ? [] : $data['flags']);

            if(isset($data['original_thread_post_id']))
                $PostObject->OriginalPostThreadID = $data['original_thread_post_id'];

            if(isset($data['priority_level']))
                $PostObject->PriorityLevel = $data['priority_level'];

            if(isset($data['text_entities']))
            {
                foreach($data['text_entities'] as $textEntity)
                {
                    $PostObject->TextEntities[] = TextEntity::fromArray($textEntity);
                }
            }

            if(isset($data['like_count']))
                $PostObject->LikeCount = ($data['like_count'] == null ? 0 : (int)$data['like_count']);

            if(isset($data['repost_count']))
                $PostObject->RepostCount = ($data['repost_count'] == null ? 0 : (int)$data['repost_count']);

            if(isset($data['quote_count']))
                $PostObject->QuoteCount = ($data['quote_count'] == null ? 0 : (int)$data['quote_count']);

            if(isset($data['reply_count']))
                $PostObject->ReplyCount = ($data['reply_count'] == null ? 0 : (int)$data['reply_count']);

            if(isset($data['attachments']))
                $PostObject->Attachments = ($data['attachments'] == null ? [] : $data['attachments']);

            if(isset($data['count_last_updated_timestamp']))
                $PostObject->CountLastUpdatedTimestamp = ($data['count_last_updated_timestamp'] == null ? 0 : (int)$data['count_last_updated_timestamp']);

            if(isset($data['last_updated_timestamp']))
                $PostObject->LastUpdatedTimestamp = ($data['last_updated_timestamp'] == null ? null : (int)$data['last_updated_timestamp']);

            if(isset($data['created_timestamp']))
                $PostObject->CreatedTimestamp = ($data['created_timestamp'] == null ? null : (int)$data['created_timestamp']);

            return $PostObject;
        }

        /**
         * Returns an alternative representation of an objects array
         *
         * @return array
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function toArrayAlternative(): array
        {
            $text_entities = [];

            foreach($this->TextEntities as $textEntity)
                $text_entities[] = $textEntity->toArray();

            return [
                //'id' => ($this->ID == null ? null : (int)$this->ID),
                'public_id' => $this->PublicID,
                'text' => $this->Text,
                'source' => $this->Source,
                'properties' => ($this->Properties == null ? null : $this->Properties->toArray()),
                'session_id' => ($this->SessionID == null ? null : $this->SessionID),
                'poster_user_id' => $this->PosterUserID,
                'reply_to_post_id' => ($this->Reply == null ? null : $this->Reply->ReplyToPostID),
                'reply_to_user_id' => ($this->Reply == null ? null : $this->Reply->ReplyToUserID),
                'quote_original_post_id' => ($this->Quote == null ? null : $this->Quote->OriginalPostID),
                'quote_original_user_id' => ($this->Quote == null ? null : $this->Quote->OriginalUserID),
                'repost_original_post_id' => ($this->Repost == null ? null : $this->Repost->OriginalPostID),
                'repost_original_user_id' => ($this->Repost == null ? null : $this->Repost->OriginalUserID),
                'original_thread_post_id' => ($this->OriginalPostThreadID == null ? null : $this->OriginalPostThreadID),
                'flags' => ($this->Flags == null ? [] : $this->Flags),
                'priority_level' => ($this->Flags == null ? [] : $this->Flags),
                'text_entities' => ($this->TextEntities == null ? [] : $text_entities),
                'like_count' => ($this->LikeCount == null ? 0 : (int)$this->LikeCount),
                'repost_count' => ($this->RepostCount == null ? 0 : (int)$this->RepostCount),
                'quote_count' => ($this->QuoteCount == null ? 0 : (int)$this->QuoteCount),
                'reply_count' => ($this->ReplyCount == null ? 0 : (int)$this->ReplyCount),
                'attachments' => ($this->Attachments == null ? [] : $this->Attachments),
                'count_last_updated_timestamp' => ($this->CountLastUpdatedTimestamp == null ? 0 : $this->CountLastUpdatedTimestamp),
                'last_updated_timestamp' => ($this->LastUpdatedTimestamp == null ? null : $this->LastUpdatedTimestamp),
                'created_timestamp' => ($this->CreatedTimestamp == null ? null : $this->CreatedTimestamp)
            ];
        }

        /** @noinspection DuplicatedCode */
        public static function fromAlternativeArray(array $data): Post
        {
            $PostObject = new Post();
            $PostObject->TextEntities = [];
            $PostObject->Attachments = [];

            //if(isset($data['id']))
            //    $PostObject->ID = ($data['id'] == null ? null : (int)$data['id']);

            if(isset($data['public_id']))
                $PostObject->PublicID = $data['public_id'];

            if(isset($data['text']))
                $PostObject->Text = $data['text'];

            if(isset($data['source']))
                $PostObject->Source = $data['source'];

            if(isset($data['properties']))
                $PostObject->Properties = ($data['properties'] == null ? new Properties() : Properties::fromArray($data['properties']));

            if(isset($data['session_id']))
                $PostObject->SessionID = ($data['session_id'] == null ? null : $data['session_id']);

            if(isset($data['poster_user_id']))
                $PostObject->PosterUserID = ($data['poster_user_id'] == null ? null : (int)$data['poster_user_id']);

            if(isset($data['reply_to_post_id']))
            {
                if($PostObject->Reply == null)
                    $PostObject->Reply = new Reply();
                $PostObject->Reply->ReplyToPostID = $data['reply_to_post_id'];
            }

            if(isset($data['reply_to_user_id']))
            {
                if($PostObject->Reply == null)
                    $PostObject->Reply = new Reply();
                $PostObject->Reply->ReplyToUserID = (int)$data['reply_to_user_id'];
            }

            if(isset($data['quote_original_post_id']))
            {
                if($PostObject->Quote == null)
                    $PostObject->Quote = new Quote();
                $PostObject->Quote->OriginalPostID = $data['quote_original_post_id'];
            }

            if(isset($data['quote_original_user_id']))
            {
                if($PostObject->Quote == null)
                    $PostObject->Quote = new Quote();
                $PostObject->Quote->OriginalUserID = (int)$data['quote_original_user_id'];
            }

            if(isset($data['repost_original_post_id']))
            {
                if($PostObject->Repost == null)
                    $PostObject->Repost = new Repost();
                $PostObject->Repost->OriginalPostID = $data['repost_original_post_id'];
            }

            if(isset($data['repost_original_user_id']))
            {
                if($PostObject->Repost == null)
                    $PostObject->Repost = new Quote();
                $PostObject->Repost->OriginalUserID = (int)$data['repost_original_user_id'];
            }

            if(isset($data['original_thread_post_id']))
                $PostObject->OriginalPostThreadID = $data['original_thread_post_id'];

            if(isset($data['flags']))
                $PostObject->Flags = ($data['flags'] !== null ? [] : $data['flags']);

            if(isset($data['priority_level']))
                $PostObject->PriorityLevel = $data['priority_level'];

            if(isset($data['text_entities']))
            {
                foreach($data['text_entities'] as $entity)
                    $PostObject->TextEntities[] = TextEntity::fromArray($entity);
            }

            if(isset($data['like_count']))
                $PostObject->LikeCount = ($data['like_count'] == null ? 0 : $data['like_count']);

            if(isset($data['repost_count']))
                $PostObject->RepostCount = ($data['repost_count'] == null ? 0 : $data['repost_count']);

            if(isset($data['quote_count']))
                $PostObject->QuoteCount = ($data['quote_count'] == null ? 0 : $data['quote_count']);

            if(isset($data['reply_count']))
                $PostObject->ReplyCount = ($data['reply_count'] == null ? 0 : $data['reply_count']);

            if(isset($data['attachments']))
                $PostObject->Attachments = ($data['attachments'] == null ? [] : $data['attachments']);

            if(isset($data['last_updated_timestamp']))
                $PostObject->LastUpdatedTimestamp = ($data['last_updated_timestamp'] == null ? null : (int)$data['last_updated_timestamp']);

            if(isset($data['count_last_updated_timestamp']))
                $PostObject->CountLastUpdatedTimestamp = ($data['count_last_updated_timestamp'] == null ? 0 : (int)$data['count_last_updated_timestamp']);

            if(isset($data['created_timestamp']))
                $PostObject->CreatedTimestamp = ($data['created_timestamp'] == null ? null : (int)$data['created_timestamp']);

            return $PostObject;
        }
    }
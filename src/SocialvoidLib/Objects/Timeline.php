<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Objects;

    use SocialvoidLib\Abstracts\StatusStates\TimelineState;
    use SocialvoidLib\Classes\Utilities;

    /**
     * Class Timeline
     * @package SocialvoidLib\Objects
     */
    class Timeline
    {
        /**
         * The Unique Internal Database ID
         *
         * @var int
         */
        public $ID;

        /**
         * The Unique User ID
         *
         * @var int
         */
        public $UserID;

        /**
         * The current state of the timeline
         *
         * @var string|TimelineState
         */
        public $State;

        /**
         * Array of chunks for the timeline representation
         *
         * @var array
         */
        public $PostChunks;

        /**
         * A count for how many posts this timeline has had
         *
         * @var int
         */
        public $NewPosts;

        /**
         * Unix Timestamp for when this record was last updated
         *
         * @var int
         */
        public $LastUpdatedTimestamp;

        /**
         * Unix Timestamp for when this record was created
         *
         * @var int
         */
        public $CreatedTimestamp;

        /**
         * Adds a post to the timeline
         *
         * @param int $post_id
         */
        public function addPost(int $post_id)
        {
            $this->PostChunks = Utilities::addToChunk($post_id, $this->PostChunks, 
                Utilities::getIntDefinition("SOCIALVOID_LIB_TIMELINE_MAX_SIZE", 3200), 
                Utilities::getIntDefinition("SOCIALVOID_LIB_TIMELINE_CHUNK_SIZE", 20)
            );
            $this->NewPosts += 1;
        }

        /**
         * Removes a post from the timeline and rebuilds the chunks
         *
         * @param int $post_id
         */
        public function removePost(int $post_id)
        {
            $this->PostChunks = Utilities::removeFromChunk($post_id, $this->PostChunks,
                Utilities::getIntDefinition("SOCIALVOID_LIB_TIMELINE_CHUNK_SIZE", 20));
        }

        /**
         * Rebuilds the timeline chunks
         */
        public function rebuildChunks()
        {
            $this->PostChunks = Utilities::splitToChunks(Utilities::rebuildFromChunks($this->PostChunks), Utilities::getIntDefinition("SOCIALVOID_LIB_TIMELINE_CHUNK_SIZE", 20));
        }

        /**
         * Returns the full timeline without chunks
         *
         * @return array
         */
        public function getFullTimeline(): array
        {
            return Utilities::rebuildFromChunks($this->PostChunks);
        }

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
                "state" => $this->State,
                "post_chunks" => $this->PostChunks,
                "new_posts" => $this->NewPosts,
                "last_updated_timestamp" => $this->LastUpdatedTimestamp,
                "created_timestamp" => $this->CreatedTimestamp
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return Timeline
         */
        public static function fromArray(array $data): Timeline
        {
            $TimelineObject = new Timeline();

            if(isset($data["id"]))
                $TimelineObject->ID = (int)$data["id"];

            if(isset($data["user_id"]))
                $TimelineObject->UserID = (int)$data["user_id"];

            if(isset($data["state"]))
                $TimelineObject->State = $data["state"];

            if(isset($data["post_chunks"]))
                $TimelineObject->PostChunks = $data["post_chunks"];

            if(isset($data["new_posts"]))
                $TimelineObject->NewPosts = (int)$data["new_posts"];

            if(isset($data["last_updated_timestamp"]))
                $TimelineObject->LastUpdatedTimestamp = (int)$data["last_updated_timestamp"];

            if(isset($data["created_timestamp"]))
                $TimelineObject->CreatedTimestamp = (int)$data["created_timestamp"];

            $TimelineObject->rebuildChunks();
            return $TimelineObject;
        }
    }
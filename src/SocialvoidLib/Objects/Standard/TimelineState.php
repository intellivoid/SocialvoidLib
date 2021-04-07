<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    /**
     * Class TimelineState
     * @package SocialvoidLib\Objects\Standard
     */
    class TimelineState
    {
        /**
         * The current number of posts that has been on this users
         * timeline, it increases whenever a new post broadcast is received
         *
         * @var int
         */
        public $TimelinePostsCount;

        /**
         * The Unix Timestamp that indicates when this users timeline
         * was last updated
         *
         * @var int
         */
        public $TimelineLastUpdated;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "timeline_posts_count" => $this->TimelinePostsCount,
                "timeline_last_updated" => $this->TimelineLastUpdated
            ];
        }

        /**
         * Constructs object from an array representation
         * 
         * @param array $data
         * @return TimelineState
         */
        public static function fromArray(array $data): TimelineState
        {
            $RosterObject = new TimelineState();

            if(isset($data["timeline_posts_count"]))
                $RosterObject->TimelinePostsCount = $data["timeline_posts_count"];

            if(isset($data["timeline_last_updated"]))
                $RosterObject->TimelineLastUpdated = $data["timeline_last_updated"];

            return $RosterObject;
        }
    }
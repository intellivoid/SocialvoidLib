<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

namespace SocialvoidLib\Classes;

    use InvalidArgumentException;
    use SocialvoidLib\Abstracts\JobClass;
    use SocialvoidLib\Abstracts\Types\JobType;
    use SocialvoidLib\Abstracts\Types\Standard\PostType;
    use SocialvoidLib\Classes\Security\Hashing;
    use SocialvoidLib\Objects\Post;

    /**
     * Class Utilities
     * @package SocialvoidLib\Classes
     */
    class Utilities
    {
        /**
         * Determines the a boolean definition, returns the default value if all else fails
         *
         * @param string $name
         * @param bool $default_value
         * @return bool
         */
        public static function getBoolDefinition(string $name, bool $default_value=false): bool
        {
            if(defined($name))
                return (bool)constant($name);

            return $default_value;
        }

        /**
         * Determines a integer definition, returns the default value if all else fails
         *
         * @param string $name
         * @param int $default_value
         * @return int
         */
        public static function getIntDefinition(string $name, int $default_value=0): int
        {
            if(defined($name))
                return (int)constant($name);

            return $default_value;
        }

        /**
         * Generates a job ID
         *
         * @param array $data
         * @param int $timestamp
         * @return string
         */
        public static function generateJobID(array $data, int $timestamp): string
        {
            // UPDATE: crc32b takes less time and ram to calculate
            $pepper = Hashing::pepper(json_encode($data) . $timestamp);
            return hash("crc32b", $pepper . json_encode($data) . $timestamp);
        }

        /**
         * Splits an array to chunks
         *
         * @param $input
         * @param $size
         * @return array
         */
        public static function splitToChunks($input, $size): array
        {
            return array_chunk($input, $size);
        }

        /**
         * Rebuilds a chunked array into a raw array
         *
         * @param $input
         * @return array
         */
        public static function rebuildFromChunks($input): array
        {
            $results = [];
            foreach($input as $chunk)
            {
                foreach($chunk as $item)
                    $results[] = $item;
            }
            return $results;
        }

        /**
         * Adds an item to a chunked item
         *
         * @param $item
         * @param $chunks
         * @param int $max_size
         * @param int $max_chunks
         * @return array
         */
        public static function addToChunk($item, $chunks, int $max_size=175, int $max_chunks=5): array
        {
            $data = self::rebuildFromChunks($chunks);
            if(in_array($item, $data))
                return $chunks;

            array_unshift($data, $item);

            if(count($data) > $max_size)
                $data = array_pop($data);

            return self::splitToChunks($data, $max_chunks);
        }

        /**
         * Removes an item from the chunk and rebuilds it
         *
         * @param $item
         * @param $chunks
         * @param int $max_chunks
         * @return array
         */
        public static function removeFromChunk($item, $chunks, int $max_chunks=5): array
        {
            $data = self::rebuildFromChunks($chunks);
            if(in_array($item, $data) == false)
                return $chunks;

            $data = array_diff($data, [$item]);

            return self::splitToChunks($data, $max_chunks);
        }

        /**
         * Splits the job weight
         *
         * @param array $data
         * @param int $workers_available
         * @param bool $preserve_keys
         * @param int $utilization
         * @return array
         */
        public static function splitJobWeight(array $data, int $workers_available, bool $preserve_keys=false, int $utilization=50): array
        {
            // Return the same data if the amount of data cannot be split to more than one worker
            if(count($data) == 1)
                return array_chunk($data, 1, $preserve_keys);

            // Auto-correct the utilization value to prevent negative calculations (1-100)
            if($utilization > 100) $utilization = 100;
            if($utilization < 1) $utilization = 1;

            // Determines the amount of workers to be used by the utilization percentage
            $workers_available = (int)($workers_available * $utilization) / 100;

            $chunks_count = (int)round(count($data) / $workers_available);
            if($chunks_count == 0) $chunks_count = 1;
            return array_chunk($data, $chunks_count, $preserve_keys);
        }

        /**
         * Determines what class the job goes to
         *
         * @param string $job_type
         * @return string
         */
        public static function determineJobClass(string $job_type): string
        {
            switch($job_type)
            {
                case JobType::ResolveUsers:
                case JobType::ResolvePosts:
                    return JobClass::QueryClass;

                case JobType::DistributeTimelinePost:
                case JobType::RemoveTimelinePosts:
                    return JobClass::UpdateClass;

                default:
                    throw new InvalidArgumentException("The given job type does not belong to a worker class");
            }
        }

        /**
         * Determines the post type which indicates how the post is supposed
         * to be rendered for better fit the UI, this uses a standard logic
         * that is expected for libraries to use.
         *
         * @param Post $post
         * @return string
         */
        public static function determinePostType(Post $post): string
        {
            // A repost will not contain text or media, it's just a repost.
            if($post->Repost !== null && $post->Repost->OriginalPostID !== null)
                return PostType::Repost;

            // A quoted post may contain media or text and media.
            if($post->Quote !== null && $post->Quote->OriginalPostID !== null)
            {
                if($post->MediaContent !== null && count($post->MediaContent) > 0)
                    return PostType::QuoteMediaPost;

                return PostType::QuoteTextPost;
            }

            // A reply post may contain media or text and media
            if($post->Reply !== null && $post->Reply->ReplyToPostID !== null)
            {
                if($post->MediaContent !== null && count($post->MediaContent) > 0)
                    return PostType::ReplyMediaPost;

                return PostType::ReplyTextPost;
            }

            // A simple post may contain media or text and media
            if($post->MediaContent !== null && count($post->MediaContent) > 0)
                return PostType::MediaPost;

            // If all checks fail, it's safe to assume this is just a text post
            if($post->Text !== null)
                return PostType::TextPost;

            // This post may be included in a new update and the library does not
            // yet have the logic to identify the post
            return PostType::Unknown;
        }

        /**
         * Generates a Telegram CDN ID for uploaded media
         *
         * @param string $file_contents
         * @return string
         */
        public static function generateTelegramCdnId(string $file_contents): string
        {
            return Hashing::pepper($file_contents . time());
        }
    }
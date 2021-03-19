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
    use SocialvoidLib\Classes\Security\Hashing;

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
                return array_chunk($data, 1);

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
                    return JobClass::UpdateClass;

                default:
                    throw new InvalidArgumentException("The given job type does not belong to a worker class");
            }
        }
    }
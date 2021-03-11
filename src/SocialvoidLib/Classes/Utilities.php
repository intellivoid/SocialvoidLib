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
            $pepper = Hashing::pepper(json_encode($data) . $timestamp);
            return hash("sha256", $pepper . json_encode($data) . $timestamp);
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
            $chunks = array();
            $i = 0;

            foreach ($input as $value)
            {
                $chunks[(int)($i++ / $size)][] = $value;
            }

            return $chunks;
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
         * @param int $max_size
         * @param int $max_chunks
         * @return array
         */
        public static function removeFromChunk($item, $chunks, int $max_size=175, int $max_chunks=5): array
        {
            $data = self::rebuildFromChunks($chunks);
            if(in_array($item, $data) == false)
                return $chunks;

            $data = array_diff($data, [$item]);

            return self::splitToChunks($data, $max_chunks);
        }
    }
<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Objects\Timeline;

    /**
     * Class Chunk
     * @package SocialvoidLib\Objects\Timeline
     */
    class Chunk
    {
        /**
         * The chunk data
         *
         * @var array
         */
        public $Data;

        /**
         * Returns the chunk data
         *
         * @return array
         */
        public function toArray(): array
        {
            return $this->Data;
        }

        /**
         * Constructs the object from chunk data
         *
         * @param array $data
         * @return Chunk
         */
        public static function fromArray(array $data): Chunk
        {
            $ChunkObject = new Chunk();
            $ChunkObject->Data = $data;
            return $ChunkObject;
        }
    }
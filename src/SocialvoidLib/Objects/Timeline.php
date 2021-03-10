<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

namespace SocialvoidLib\Objects;

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
         * The Unique Public ID
         *
         * @var string
         */
        public $PublicID;

        /**
         * The Unique User ID
         *
         * @var int
         */
        public $UserID;

        public $PostChunks;

        public $LastUpdated;

        public $Created;
    }
<?php


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
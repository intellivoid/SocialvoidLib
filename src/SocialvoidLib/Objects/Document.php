<?php


    namespace SocialvoidLib\Objects;


    class Document
    {
        /**
         * The Public ID of this record
         *
         * @var string
         */
        public $PublicID;

        /**
         * The source of the content
         *
         * @var string
         */
        public $ContentSource;

        /**
         * The
         *
         * @var string|null
         */
        public $CdnPublicID;

        /**
         * Tje
         *
         * @var string
         */
        public $ThirdPartySource;

        public $FileMime;

        public $FileSize;

        public $FileName;

        public $FileExtension;

        public $OwnerUserID;

        public $ForwardUserID;

        public $AccessType;

        public $Flags;

        public $Properties;

        public $LastUpdatedTimestamp;

        public $LastAccessedTimestamp;

        public $CreatedTimestamp;
    }
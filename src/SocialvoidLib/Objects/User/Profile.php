<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\User;

    /**
     * Class Profile
     * @package SocialvoidLib\Objects\User
     */
    class Profile
    {
        /**
         * The first name of the user
         *
         * @var string|null
         */
        public $FirstName;

        /**
         * The last name of the user
         *
         * @var string|null
         */
        public $LastName;

        /**
         * The biography of the user (Description)
         *
         * @var string|null
         */
        public $Biography;

        /**
         * The location of the user
         *
         * @var string|null
         */
        public $Location;

        /**
         * The username of the user's Twitter Account
         *
         * @var string|null
         */
        public $TwitterUsername;

        /**
         * The username of the user's Instagram account
         *
         * @var string|null
         */
        public $InstagramUsername;

        /**
         * The username of the user's Telegram Account
         *
         * @var string|null
         */
        public $TelegramUsername;

        /**
         * The URL of the user's website
         *
         * @var string
         */
        public $WebsiteURL;
    }
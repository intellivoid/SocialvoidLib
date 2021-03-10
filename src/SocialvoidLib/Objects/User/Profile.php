<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

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
         * Indicates if the user avatar is visible or not, this will automatically be updated
         *
         * @var bool|null
         */
        public $UserAvatarVisible;

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

        /**
         * Returns a array of standard URL references
         *
         * @return array
         */
        public function getUrls(): array
        {
            $Results = [];

            if($this->TwitterUsername !== null)
            {
                // @ Check
                if(substr($this->TwitterUsername, 0, 1) == "@")
                {
                    $Results["TWITTER"] = "https://twitter.com/" . substr($this->TwitterUsername, 1);
                }
                else
                {
                    $Results["TWITTER"] = "https://twitter.com/" . $this->TwitterUsername;
                }
            }

            if($this->InstagramUsername !== null)
            {
                // @ Check
                if(substr($this->InstagramUsername, 0, 1) == "@")
                {
                    $Results["INSTAGRAM"] = "https://instagram.com/" . substr($this->InstagramUsername, 1);
                }
                else
                {
                    $Results["INSTAGRAM"] = "https://instagram.com/" . $this->InstagramUsername;
                }
            }

            if($this->TelegramUsername !== null)
            {
                // @ Check
                if(substr($this->TelegramUsername, 0, 1) == "@")
                {
                    $Results["TELEGRAM"] = "https://t.me/" . substr($this->TelegramUsername, 1);
                }
                else
                {
                    $Results["TELEGRAM"] = "https://t.me/" . $this->TelegramUsername;
                }
            }

            if($this->WebsiteURL !== null)
            {
                $Results["WEBSITE"] = $this->WebsiteURL;
            }

            return $Results;
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "user_avatar_visible" => $this->UserAvatarVisible,
                "first_name" => $this->FirstName,
                "last_name" => $this->LastName,
                "biography" => $this->Biography,
                "location" => $this->Location,
                "twitter_username" => $this->TwitterUsername,
                "instagram_username" => $this->InstagramUsername,
                "telegram_username" => $this->TelegramUsername,
                "website_url" => $this->WebsiteURL
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return Profile
         * @noinspection DuplicatedCode
         */
        public static function fromArray(array $data): Profile
        {
            $ProfileObject = new Profile();

            if(isset($data["user_avatar_visible"]))
                $ProfileObject->UserAvatarVisible = $data["user_avatar_visible"];

            if(isset($data["first_name"]))
                $ProfileObject->FirstName = $data["first_name"];

            if(isset($data["last_name"]))
                $ProfileObject->LastName = $data["last_name"];

            if(isset($data["biography"]))
                $ProfileObject->Biography = $data["biography"];

            if(isset($data["location"]))
                $ProfileObject->Location = $data["location"];

            if(isset($data["twitter_username"]))
                $ProfileObject->TwitterUsername = $data["twitter_username"];
            
            if(isset($data["instagram_username"]))
                $ProfileObject->InstagramUsername = $data["instagram_username"];

            if(isset($data["telegram_username"]))
                $ProfileObject->TelegramUsername = $data["telegram_username"];

            if(isset($data["website_url"]))
                $ProfileObject->WebsiteURL = $data["website_url"];

            return $ProfileObject;
        }

    }
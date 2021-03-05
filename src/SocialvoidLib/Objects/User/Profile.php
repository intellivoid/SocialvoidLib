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
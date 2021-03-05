<?php


    namespace SocialvoidLib\Objects\User;

    /**
     * Class UserSettings
     * @package SocialvoidLib\Objects\User
     */
    class UserSettings
    {
        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [];
        }

        /**
         * Constructs an object from an array representation
         *
         * @param array $data
         * @return UserSettings
         */
        public static function fromArray(array $data): UserSettings
        {
            $UserSettingsObject = new UserSettings();

            return $UserSettingsObject;
        }
    }
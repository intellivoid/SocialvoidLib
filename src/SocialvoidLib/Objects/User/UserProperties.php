<?php

    /** @noinspection PhpUnused */

namespace SocialvoidLib\Objects\User;

    /**
     * Class UserProperties
     * @package SocialvoidLib\Objects\User
     */
    class UserProperties
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
         * Constructs the user properties object from an array representation
         *
         * @param array $data
         * @return UserProperties
         */
        public static function fromArray(array $data): UserProperties
        {
            $UserPropertiesObject = new UserProperties();

            return $UserPropertiesObject;
        }
    }
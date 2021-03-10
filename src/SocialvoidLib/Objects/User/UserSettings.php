<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

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
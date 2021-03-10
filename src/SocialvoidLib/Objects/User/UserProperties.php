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
<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

namespace SocialvoidLib\Objects\Post;

    /**
     * Class Properties
     * @package SocialvoidLib\Objects\Post
     */
    class Properties
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
         * Constructs a object from an array representation
         *
         * @param array $data
         * @return Properties
         */
        public static function fromArray(array $data): Properties
        {
            $PropertiesObject = new Properties();

            return $PropertiesObject;
        }
    }
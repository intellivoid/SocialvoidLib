<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

namespace SocialvoidLib\Abstracts\SearchMethods;

    /**
     * Class UserSearchMethod
     * @package SocialvoidLib\Abstracts\SearchMethods
     */
    abstract class UserSearchMethod
    {
        /**
         * Searches a user by the Unique Internal Database ID
         */
        const ById = "id";

        /**
         * Searches a user by the Unique Public ID associated with the network
         */
        const ByPublicId = "public_id";

        /**
         * Searches a user by the Username (Automatically converted to safe)
         */
        const ByUsername = "username_safe";
    }
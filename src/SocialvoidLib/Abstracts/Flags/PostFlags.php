<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Abstracts\Flags;

    /**
     * Class PostFlags
     * @package SocialvoidLib\Abstracts\Flags
     */
    class PostFlags
    {
        /**
         * Indicates if the post is currently liked by the user
         */
        const Liked = "LIKED";

        /**
         * Indicates if the post is currently reposted by the user
         */
        const Reposted = "REPOSTED";

        /**
         * This indicates if the post was deleted
         */
        const Deleted = "DELETED";
    }
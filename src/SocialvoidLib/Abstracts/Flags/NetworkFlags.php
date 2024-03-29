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
     * Class NetworkFlags
     * @package SocialvoidLib\Abstracts\Flags
     */
    abstract class NetworkFlags
    {
        /**
         * Indicates that a user is currently authenticated to the network
         */
        const Authenticated = "AUTHENTICATED";

        /**
         * Indicates that the user is not authenticated to the network
         */
        const Unauthenticated = "UNAUTHENTICATED";
    }
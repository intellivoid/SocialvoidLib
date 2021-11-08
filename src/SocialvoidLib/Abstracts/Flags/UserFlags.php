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


    namespace SocialvoidLib\Abstracts\Flags;

    /**
     * Class UserFlags
     * @package SocialvoidLib\Abstracts\Flags
     */
    abstract class UserFlags
    {
        /**
         * Indicates if the user is verified
         */
        const Verified = "VERIFIED";

        /**
         * Indicates if the user is an official entity
         * representation of the network
         */
        const Official = "OFFICIAL";
    }
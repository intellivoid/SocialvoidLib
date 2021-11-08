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
         * Indicates if the user is a verified entity on the network, verification doesn't give any additional abilities,
         * it is simply one of the ways to demonstrate that the user is official.
         */
        const Verified = "VERIFIED";
    }
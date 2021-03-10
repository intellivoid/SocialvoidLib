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

    namespace SocialvoidLib\Abstracts\StatusStates;

    /**
     * The current status of the user
     *
     * Class UserStatus
     * @package SocialvoidLib\Abstracts\StatusStates
     */
    abstract class UserStatus
    {
        /**
         * The user is currently active and can browse the network
         */
        const Active = "ACTIVE";

        /**
         * The user is currently banned and cannot access the network
         */
        const Banned = "BANNED";

        /**
         * The user is banned for a x amount of time, the user's profile
         * will not be shown as banned, this will be lifted once the user
         * invokes the next request at the time the ban is lifted
         */
        const TemporarilyBanned = "TEMPORARILY_BANNED";

        /**
         * The user can browse the network but cannot invoke any posts or
         * activities against other users.
         */
        const Restricted = "RESTRICTED";

        /**
         * The user can browse the network but cannot invoke any posts or
         * activities against other users for an x amount o time, this will
         * be lifted once the user invokes the next request at the time the
         * restriction is lifted
         */
        const TemporarilyRestricted = "TEMPORARILY_RESTRICTED";
    }
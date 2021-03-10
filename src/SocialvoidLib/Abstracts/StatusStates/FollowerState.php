<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

namespace SocialvoidLib\Abstracts\StatusStates;

    /**
     * Class FollowerState
     * @package SocialvoidLib\Abstracts\StatusStates
     */
    abstract class FollowerState
    {
        /**
         * The user is currently following the other user and can see posts even if the other
         * user is private.
         */
        const Following = "FOLLOWING";

        /**
         * The target user is currently private and needs to approve the following request
         */
        const AwaitingApproval = "AWAITING_APPROVAL";

        /**
         * The current user unfollowed the target user
         */
        const Unfollowed = "UNFOLLOWED";
    }
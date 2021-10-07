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
         * There is no relationship between the two peers
         */
        const None = 0;

        /**
         * The peer is current following the target peer
         */
        const Following = 1;

        /**
         * The peer is awaiting an approval to follow the target peer
         */
        const AwaitingApproval = 2;

        /**
         * The peer blocked the target peer
         */
        const Blocked = 3;
    }
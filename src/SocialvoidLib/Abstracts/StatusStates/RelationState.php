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
    abstract class RelationState
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
         * The peer is followed by the target user
         */
        const FollowsYou = 2;

        /**
         * The peer is awaiting an approval to follow the target peer
         */
        const AwaitingApproval = 3;

        /**
         * Both peers are following each other
         */
        const MutuallyFollowing = 4;

        /**
         * The peer has blocked the target peer (Only the target peer will see this)
         */
        const Blocked = 5;

        /**
         * The target peer blocked the peer
         */
        const BlockedYou = 6;
    }
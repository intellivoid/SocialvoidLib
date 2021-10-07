<?php

    namespace SocialvoidLib\Abstracts\Types\Standard;

    abstract class RelationshipType
    {
        /**
         * There is no relationship between the two peers
         */
        const None = "NONE";

        /**
         * The peer is current following the target peer
         */
        const Following = "FOLLOWING";

        /**
         * The peer is followed by the target user
         */
        const FollowsYou = "FOLLOWS_YOU";

        /**
         * The peer is awaiting an approval to follow the target peer
         */
        const AwaitingApproval = "AWAITING_APPROVAL";

        /**
         * Both peers are following each other
         */
        const MutuallyFollowing = "MUTUALLY_FOLLOWING";

        /**
         * The peer has blocked the target peer (Only the target peer will see this)
         */
        const Blocked = "BLOCKED";

        /**
         * The target peer blocked the peer
         */
        const BlockedYou = "BLOCKED_YOU";
    }
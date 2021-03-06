<?php


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
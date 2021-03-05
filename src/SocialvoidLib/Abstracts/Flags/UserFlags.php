<?php

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

        /**
         * Indicates if the user is a support user dedicated for
         * support queries about the network or organization
         */
        const Support = "SUPPORT";

        /**
         * Indicates if the user is a proxy account that mirrors posts
         * from another network
         */
        const Proxy = "PROXY";

        /**
         * Indicates if the user is an administrator with elevated permissions
         * to the network
         */
        const Administrator = "ADMINISTRATOR";

        /**
         * Indicates if the user is a moderator on the network with permissions
         * for moderation purposes, not the same set permissions as a administrator
         */
        const Moderator = "MODERATOR";

        /**
         * Indicates if this user hosts content that isn't NSFW
         */
        const NotSafeForWork = "NSFW";
    }
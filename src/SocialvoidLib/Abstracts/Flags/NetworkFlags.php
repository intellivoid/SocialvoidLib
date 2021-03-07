<?php


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
         * Indicates that the user has administrator access to the network
         */
        const AdministratorAccess = "ADMINISTRATOR_ACCESS";

        /**
         * Indicates that the user has moderator access to the network
         */
        const ModeratorAccess = "MODERATOR_ACCESS";

        /**
         * Indicates that the user is currently browsing the network as a guest (Read-only)
         */
        const GuestAccess = "GUEST_ACCESS";
    }
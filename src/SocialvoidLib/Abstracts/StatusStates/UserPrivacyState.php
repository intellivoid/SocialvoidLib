<?php


    namespace SocialvoidLib\Abstracts\StatusStates;

    /**
     * Class UserPrivacyState
     * @package SocialvoidLib\Abstracts\StatusStates
     */
    abstract class UserPrivacyState
    {
        /**
         * The user is publicly available to view and so is their posts
         */
        const Public = "PUBLIC";

        /**
         * The user is publicly available but their posts are made private and so are the
         * users they follow or is following them.
         */
        const Private = "PRIVATE";

        /**
         * The user will appear on public comments and interactions but the profile information
         * cannot be obtained directly and the user profile will appear to not exist.
         */
        const Ghost = "GHOST";
    }
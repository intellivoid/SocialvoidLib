<?php


    namespace SocialvoidLib\Abstracts\SearchMethods;

    /**
     * Class UserSearchMethod
     * @package SocialvoidLib\Abstracts\SearchMethods
     */
    abstract class UserSearchMethod
    {
        /**
         * Searches a user by the Unique Internal Database ID
         */
        const ById = "id";

        /**
         * Searches a user by the Unique Public ID associated with the network
         */
        const ByPublicId = "public_id";

        /**
         * Searches a user by the Username (Automatically converted to safe)
         */
        const ByUsername = "username_safe";
    }
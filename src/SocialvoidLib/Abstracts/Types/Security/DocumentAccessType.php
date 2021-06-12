<?php


    namespace SocialvoidLib\Abstracts\Types\Security;

    /**
     * Class DocumentAccessType
     * @package SocialvoidLib\Abstracts\Types\Security
     */
    abstract class DocumentAccessType
    {
        /**
         * Indicates that the object is accessible to the public, even to users that aren't authenticated.
         */
        const Public = "PUBLIC";

        /**
         * Indicates that the object is accessible but it depends on the current status of the user and post,
         * eg if the user is private then only users that are following the user can see the post. The access
         * roles blob will contain more information about this
         */
        const Protected = "PROTECTED";

        /**
         * This object is only accessible to the parties specified in the access roles blob
         */
        const Private = "PRIVATE";
    }
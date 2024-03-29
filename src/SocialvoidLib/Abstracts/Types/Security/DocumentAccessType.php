<?php


    namespace SocialvoidLib\Abstracts\Types\Security;

    /**
     * Class DocumentAccessType
     * @package SocialvoidLib\Abstracts\Types\Security
     */
    abstract class DocumentAccessType
    {
        /**
         * Indicates that nobody has access to the document until the server sets the appropriate permissions
         */
        const None = "NONE";

        /**
         * Indicates that the object is accessible to the public, even to users that aren't authenticated.
         */
        const Public = "PUBLIC";

        /**
         * This object is only accessible to the parties specified in the access roles blob
         */
        const Private = "PRIVATE";
    }
<?php


    namespace SocialvoidLib\Abstracts;

    /**
     * Class JobClass
     * @package SocialvoidLib\Abstracts
     */
    abstract class JobClass
    {
        /**
         * Worker classes used for searching and fetching information
         */
        const QueryClass = "query";

        /**
         * Worker classes used for updating information (Not meant to return data)
         */
        const UpdateClass = "update";

        /**
         * Worker classes used for heavy jobs (Updating and queries) when the return
         * information is not important
         */
        const HeavyClass = "heavy";
    }
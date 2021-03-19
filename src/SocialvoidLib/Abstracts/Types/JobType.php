<?php


    namespace SocialvoidLib\Abstracts\Types;

    /**
     * Class JobType
     * @package SocialvoidLib\Abstracts\Types
     */
    abstract class JobType
    {
        /**
         * Resolves multiple users
         */
        const ResolveUsers = 0x001;

        /**
         * Distributes a post to multiple timelines
         */
        const DistributeTimelinePost = 0x002;

        /**
         * Resolves multiple posts
         */
        const ResolvePosts = 0x003;
    }
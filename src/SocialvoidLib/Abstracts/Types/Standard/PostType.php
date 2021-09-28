<?php


    namespace SocialvoidLib\Abstracts\Types\Standard;

    /**
     * Class PostType
     * @package SocialvoidLib\Abstracts\Types\Standard
     */
    abstract class PostType
    {
        /**
         * Undocumented post type
         */
        const Unknown = "UNKNOWN";

        /**
         * Indicates that the post was deleted
         */
        const Deleted = "DELETED";

        /**
         * Indicates that this is a ordinary text post
         */
        const Post = "POST";

        /**
         * Indicates that this post is a reply with just text
         */
        const Reply = "REPLY";

        /**
         * Indicates that this post is a quote of another post
         * with just text
         */
        const Quote = "QUOTE";

        /**
         * Indicates that this post is simply a repost and the
         * post itself should not be treated as a post, the
         * repost property should be treated as the original post.
         */
        const Repost = "REPOST";
    }
<?php


    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Types\Standard\PostType;

    /**
     * Class Post
     * @package SocialvoidLib\Objects\Standard
     */
    class Post
    {
        /**
         * The Public ID of the post
         *
         * @var int
         */
        public $ID;

        /**
         *
         * The array of flags associated with this post
         *
         * @var array|string[]
         */
        public $Flags;

        /**
         * The type of post this is
         *
         * @var string|PostType
         */
        public $PostType;

        /**
         * The text of the post, this can be null for reposts
         *
         * @var string|null
         */
        public $Text;

        /**
         * The source client of the post
         *
         * @var string
         */
        public $Source;

        /**
         * The peer author of the post
         *
         * @var Peer
         */
        public $PeerAuthor;

        /**
         * The post that this post is replying to
         *
         * @var Post|null
         */
        public $ReplyToPost;

        /**
         * The post that this post is quoting
         *
         * @var Post|null
         */
        public $QuoteOriginalPost;

        /**
         * The original post that this post is reposting
         *
         * @var Post|null
         */
        public $RepostOriginalPost;

        /**
         * The amount of like this post got, this can be null
         * if the post is a repost
         *
         * @var int|null
         */
        public $LikesCount;

        /**
         * The amount of reposts this post got, this can be null
         * if the post is a repost
         *
         * @var int|null
         */
        public $RepostsCount;

        /**
         * The amount of reposts this post got, this can be null if
         * the post is is a repost
         *
         * @var int|null
         */
        public $QuotesCount;

        /**
         * The amount of replies this post got, this can be null
         * if the post is a repost
         *
         * @var int|null
         */
        public $RepliesCount;

        /**
         * The Unix Timestamp when this post was posted
         *
         * @var int|null
         */
        public $PostedTimestamp;
    }
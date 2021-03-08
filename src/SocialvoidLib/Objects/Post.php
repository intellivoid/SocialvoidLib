<?php


    namespace SocialvoidLib\Objects;

    use SocialvoidLib\Objects\Post\Entities;
    use SocialvoidLib\Objects\Post\Quote;
    use SocialvoidLib\Objects\Post\Reply;
    use SocialvoidLib\Objects\Post\Repost;

    /**
     * Class Post
     * @package SocialvoidLib\Objects
     */
    class Post
    {
        /**
         * The Unique Internal Database ID for this post
         *
         * @var int
         */
        public $ID;

        /**
         * @var
         */
        public $PublicID;

        /**
         * The actual UTF-8 text of the status update
         *
         * @var string|null
         */
        public $Text;

        /**
         * The device used to post this
         *
         * @var string
         */
        public $Source;

        /**
         * The original author of this post
         *
         * @var int
         */
        public $PosterUserID;

        /**
         * Information about this post's reply status
         *
         * @var Reply|null
         */
        public $Reply;

        /**
         * Information about the quote this post is mentioning
         *
         * @var Quote|null
         */
        public $Quote;

        /**
         * Information about this post being a repost
         *
         * @var Repost|null
         */
        public $Repost;

        /**
         * Flags associated with this post
         *
         * @var array
         */
        public $Flags;

        /**
         * The level of this post's priority
         *
         * @var string
         */
        public $PriroityLevel;

        /**
         * @var Entities
         */
        public $Entities;

        /**
         * Array of User IDs that liked this post
         *
         * @var int[]
         */
        public $Likes;

        /**
         * Array of user IDs that reposted this post
         *
         * @var int[]
         */
        public $Reposts;

        /**
         * The Unix Timestamp of when this was posted
         *
         * @var int
         */
        public $CreatedTimestamp;

        public function toArray(): array
        {

        }
    }
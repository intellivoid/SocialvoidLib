<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Flags\PostFlags;
    use SocialvoidLib\Abstracts\Types\Standard\PostType;
    use SocialvoidLib\Classes\Converter;
    use SocialvoidLib\Classes\Utilities;

    /**
     * Class Post
     * @package SocialvoidLib\Objects\Standard
     */
    class Post
    {
        /**
         * The Public ID of the post
         *
         * @var string
         */
        public $ID;

        /**
         * The type of post this is
         *
         * @var string|PostType
         */
        public $PostType;

        /**
         * The peer author of the post
         *
         * @var Peer|null
         */
        public $Peer;

        /**
         * The source client of the post
         *
         * @var string
         */
        public $Source;

        /**
         * The text of the post, this can be null for reposts
         *
         * @var string|null
         */
        public $Text;

        /**
         * An array of text entities
         *
         * @var TextEntity[]
         */
        public $Entities;

        /**
         * An array of resolved mentions
         *
         * @var Peer[]
         */
        public $MentionedPeers;

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
        public $QuotedPost;

        /**
         * The original post that this post is reposting
         *
         * @var Post|null
         */
        public $RepostedPost;

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
         * the post is a repost
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

        /**
         *
         * The array of flags associated with this post
         *
         * @var array|string[]
         */
        public $Flags;

        /**
         * Returns an array representation o the object
         *
         * @return array
         */
        public function toArray(): array
        {
            $entities = [];
            foreach($this->Entities as $textEntity)
                $entities[] = $textEntity->toArray();

            $mentions = [];

            if($this->MentionedPeers !== null)
                foreach($this->MentionedPeers as $mentionedPeer)
                    $mentions[] = $mentionedPeer->toArray();

            return [
                'id' => $this->ID,
                'type' => $this->PostType,
                'peer' => ($this->Peer == null ? null : $this->Peer->toArray()),
                'source' => $this->Source,
                'text' => $this->Text,
                'entities' => $entities,
                'mentioned_peers' => $mentions,
                'reply_to_post' => ($this->ReplyToPost == null ? null : $this->ReplyToPost->toArray()),
                'quoted_post' => ($this->QuotedPost == null ? null : $this->QuotedPost->toArray()),
                'reposted_post' => ($this->RepostedPost == null ? null :$this->RepostedPost->toArray()),
                'likes_count' => (is_null($this->LikesCount) ? null : (int)$this->LikesCount),
                'reposts_count' => (is_null($this->RepostsCount) ? null : (int)$this->RepostsCount),
                'quotes_count' => (is_null($this->QuotesCount) ? null : (int)$this->QuotesCount),
                'replies_count' => (is_null($this->RepliesCount) == null ? 0 : (int)$this->RepliesCount),
                'posted_timestamp' => ($this->PostedTimestamp),
                'flags' => $this->Flags,
            ];
        }

        /**
         * Constructs object from a array representation
         *
         * @param array $data
         * @return Post
         */
        public static function fromArray(array $data): Post
        {
            $PostObject = new Post();

            if(isset($data['id']))
                $PostObject->ID = $data['id'];

            if(isset($data['type']))
                $PostObject->PostType = $data['type'];

            if(isset($data['peer']))
                $PostObject->Peer = ($data['peer'] == null ? null : Peer::fromArray($data['peer']));

            if(isset($data['source']))
                $PostObject->Source = $data['source'];

            if(isset($data['text']))
                $PostObject->Text = $data['text'];

            if(isset($data['entities']))
            {
                $PostObject->Entities = [];
                foreach($data['entities'] as $entity)
                    $PostObject->Entities[] = TextEntity::fromArray($entity);
            }

            if(isset($data['mentioned_peers']))
            {
                $PostObject->Entities = [];
                foreach($data['entities'] as $entity)
                    $PostObject->Entities[] = TextEntity::fromArray($entity);
            }

            if(isset($data['reply_to_post']))
                $PostObject->ReplyToPost = ($data['reply_to_post'] == null ? null : Post::fromArray($data['reply_to_post']));

            if(isset($data['quoted_post']))
                $PostObject->QuotedPost = ($data['quoted_post'] == null ? null : Post::fromArray($data['quoted_post']));

            if(isset($data['reposted_post']))
                $PostObject->RepostedPost = ($data['reposted_post'] == null ? null : Post::fromArray($data['reposted_post']));

            if(isset($data['likes_count']))
                $PostObject->LikesCount = $data['likes_count'];

            if(isset($data['reposts_count']))
                $PostObject->RepostsCount = $data['reposts_count'];

            if(isset($data['quotes_count']))
                $PostObject->QuotesCount = $data['quotes_count'];

            if(isset($data['replies_count']))
                $PostObject->RepliesCount = $data['replies_count'];

            if(isset($data['posted_timestamp']))
                $PostObject->PostedTimestamp = $data['posted_timestamp'];

            if(isset($data['flags']))
                $PostObject->Flags = $data['flags'];

            return $PostObject;
        }

        /**
         * Attempts to construct the standard post from a internal post object
         * this function will not attempt to resolve the sub-ids such as the original
         * poster id, the post IDs, etc.
         *
         * @param \SocialvoidLib\Objects\Post $post
         * @return Post
         */
        public static function fromPost(\SocialvoidLib\Objects\Post $post): Post
        {
            $StandardPostObject = new Post();

            $StandardPostObject->ID = $post->PublicID;
            $StandardPostObject->PostType = Utilities::determinePostType($post);
            $StandardPostObject->Text = $post->Text;
            $StandardPostObject->Entities = [];
            $StandardPostObject->MentionedPeers = [];
            $StandardPostObject->Source = $post->Source;
            $StandardPostObject->LikesCount = $post->LikesCount;
            $StandardPostObject->RepostsCount = $post->RepostsCount;
            $StandardPostObject->QuotesCount = $post->QuotesCount;
            $StandardPostObject->RepliesCount = $post->RepliesCount;
            $StandardPostObject->PostedTimestamp = $post->CreatedTimestamp;
            $StandardPostObject->Flags = $post->Flags;

            if($post->TextEntities !== null)
            {
                foreach($post->TextEntities as $entity)
                {
                    $StandardPostObject->Entities[] = TextEntity::fromArray($entity->toArray());
                }
            }

            // If the post has been deleted, remove the text, source, likes and reposts.
            // But leave the rest to keep a consistent timeline, eg; when a user
            // replies to a deleted post it should show as is but without the post contents
            if(Converter::hasFlag($StandardPostObject->Flags, PostFlags::Deleted))
            {
                Converter::removeFlag($StandardPostObject->Flags, PostFlags::Deleted);

                $StandardPostObject->PostType = PostType::Deleted;
                $StandardPostObject->Text = null;
                $StandardPostObject->Peer = null;
                $StandardPostObject->Source = null;
                $StandardPostObject->MentionedPeers = null;
                $StandardPostObject->LikesCount = 0;
                $StandardPostObject->RepliesCount = 0;
                $StandardPostObject->ReplyToPost = null;
                $StandardPostObject->RepostedPost = null;
                $StandardPostObject->RepostsCount = 0;
                $StandardPostObject->QuotedPost = null;
                $StandardPostObject->QuotesCount = 0;
            }

            return $StandardPostObject;
        }
    }
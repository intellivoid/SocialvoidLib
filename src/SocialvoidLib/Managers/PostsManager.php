<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    /** @noinspection PhpUnused */

    namespace SocialvoidLib\Managers;

    use Exception;
    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\Flags\PostFlags;
    use SocialvoidLib\Abstracts\Levels\PostPriorityLevel;
    use SocialvoidLib\Abstracts\SearchMethods\PostSearchMethod;
    use SocialvoidLib\Abstracts\Types\CacheEntryObjectType;
    use SocialvoidLib\Classes\Converter;
    use SocialvoidLib\Classes\PostText\Extractor;
    use SocialvoidLib\Classes\PostText\TwitterMethod\Parser;
    use SocialvoidLib\Classes\Standard\BaseIdentification;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Exceptions\GenericInternal\BackgroundWorkerNotEnabledException;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\CacheMissedException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\DependencyError;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\GenericInternal\RedisCacheException;
    use SocialvoidLib\Exceptions\GenericInternal\ServiceJobException;
    use SocialvoidLib\Exceptions\Internal\RepostRecordNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\AlreadyRepostedException;
    use SocialvoidLib\Exceptions\Standard\Network\PostDeletedException;
    use SocialvoidLib\Exceptions\Standard\Network\PostNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPostTextException;
    use SocialvoidLib\InputTypes\RegisterCacheInput;
    use SocialvoidLib\Objects\Post;
    use SocialvoidLib\SocialvoidLib;
    use ZiProto\ZiProto;

    /**
     * Class PostsManager
     * @package SocialvoidLib\Managers
     */
    class PostsManager
    {

        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * PostsManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Publishes a post to the network
         *
         * @param int $user_id
         * @param string $source
         * @param string $text
         * @param int|null $session_id
         * @param array $media_content
         * @param string $priority
         * @param array $flags
         * @return Post
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws PostNotFoundException
         * @throws InvalidPostTextException
         * @throws CacheException
         */
        public function publishPost(int $user_id, string $source, string $text, int $session_id=null, array $media_content=[], $priority=PostPriorityLevel::None, $flags=[]): Post
        {
            $timestamp = (int)time();

            $textPostParser = new Parser();
            $textPostResults = $textPostParser->parseInput($text);
            if($textPostResults->valid == false)
                throw new InvalidPostTextException("The given post text is invalid", $text);

            $PublicID = BaseIdentification::PostID($user_id, $timestamp, $text);
            $Properties = new Post\Properties();

            // Extract important information from this text
            $Extractor = new Extractor($text);
            $Entities = new Post\Entities();
            $Entities->Hashtags = $Extractor->extractHashtags();
            $Entities->UserMentions = $Extractor->extractMentionedUsernames();
            $Entities->Urls = $Extractor->extractURLs();

            $MediaContentArray = [];
            /** @var Post\MediaContent $value */
            foreach($media_content as $value)
                $MediaContentArray[] = $value->toArray();

            $Query = QueryBuilder::insert_into("posts", [
                "public_id" => $this->socialvoidLib->getDatabase()->real_escape_string($PublicID),
                "text" => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($text)),
                "source" => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($source)),
                "session_id" => ($session_id == null ? null : (int)$session_id),
                "poster_user_id" => (int)$user_id,
                "properties" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($Properties->toArray())),
                "flags" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($flags)),
                "is_deleted" => (int)false,
                "priority_level" => $this->socialvoidLib->getDatabase()->real_escape_string($priority),
                "entities" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($Entities->toArray())),
                "likes" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "reposts" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "quotes" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "replies" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "media_content" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($MediaContentArray)),
                "last_updated_timestamp" => $timestamp,
                "created_timestamp" => $timestamp
            ]);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);
            if($QueryResults)
            {
                $returnResults = $this->getPost(PostSearchMethod::ByPublicId, $PublicID);
                $this->registerPostCacheEntry($returnResults);
                return $returnResults;
            }
            else
            {
                throw new DatabaseException("There was an error while trying to create a post",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Returns an existing post from the network
         *
         * @param string $search_method
         * @param string $value
         * @return Post
         * @throws CacheException
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws PostNotFoundException
         */
        public function getPost(string $search_method, string $value): Post
        {
            switch($search_method)
            {
                case PostSearchMethod::ByPublicId:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = $this->socialvoidLib->getDatabase()->real_escape_string($value);
                    break;

                case PostSearchMethod::ById:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = (int)$value;
                    break;

                default:
                    throw new InvalidSearchMethodException("The given search method is invalid for getPost()", $search_method, $value);
            }

            if($this->socialvoidLib->getRedisBasicCacheConfiguration()["Enabled"])
            {
                $CachedPost = $this->getPostCacheEntry($value);
                if($CachedPost !== null) return $CachedPost;
            }

            $Query = QueryBuilder::select("posts", [
                "id",
                "public_id",
                "text",
                "source",
                "properties",
                "session_id",
                "poster_user_id",
                "reply_to_post_id",
                "reply_to_user_id",
                "quote_original_post_id",
                "quote_original_user_id",
                "repost_original_post_id",
                "repost_original_user_id",
                "flags",
                "priority_level",
                "entities",
                "likes",
                "reposts",
                "quotes",
                "replies",
                "media_content",
                "last_updated_timestamp",
                "created_timestamp"
            ], $search_method, $value, null, null, 1);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new PostNotFoundException("The requested post was not found");
                }
                else
                {
                    $Row["properties"] = ($Row["properties"] == null ? null : ZiProto::decode($Row["properties"]));
                    $Row["flags"] = ($Row["flags"] == null ? null : ZiProto::decode($Row["flags"]));
                    $Row["entities"] = ($Row["entities"] == null ? null : ZiProto::decode($Row["entities"]));
                    $Row["likes"] = ($Row["likes"] == null ? null : ZiProto::decode($Row["likes"]));
                    $Row["reposts"] = ($Row["reposts"] == null ? null : ZiProto::decode($Row["reposts"]));
                    $Row["quotes"] = ($Row["quotes"] == null ? null : ZiProto::decode($Row["quotes"]));
                    $Row["replies"] = ($Row["replies"] == null ? null : ZiProto::decode($Row["replies"]));
                    $Row["media_content"] = ($Row["media_content"] == null ? null : ZiProto::decode($Row["media_content"]));
                    $Row["text"] = ($Row["text"] == null ? null : urldecode($Row["text"]));
                    $Row["source"] = ($Row["source"] == null ? null : urldecode($Row["source"]));

                    $returnResults = Post::fromAlternativeArray($Row);
                    $this->registerPostCacheEntry($returnResults);
                    return $returnResults;
                }
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying retrieve an existing post from the network",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Updates an existing post on the network
         *
         * @param Post $post
         * @return Post
         * @throws DatabaseException
         * @throws CacheException
         */
        public function updatePost(Post $post): Post
        {
            $MediaContent = null;

            if($post->MediaContent !== null)
            {
                $MediaContent = [];
                foreach($post->MediaContent as $mediaContent)
                    $MediaContent[] = $mediaContent->toArray();
            }

            $post->LastUpdatedTimestamp = (int)time();
            
            // TODO: Validate text
            // Probably the most CPU intensive update there is here.
            $Query = QueryBuilder::update("posts", [
                "text" => ($post->Text == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($post->Text))),
                "source" => ($post->Source == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($post->Source))),
                "properties" => ($post->Properties == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($post->Properties->toArray()))),
                "poster_user_id" => ($post->PosterUserID == null ? null : (int)$post->PosterUserID),
                "reply_to_post_id" => ($post->Reply == null || $post->Reply->ReplyToPostID == null ? null : (int)$post->Reply->ReplyToPostID),
                "reply_to_user_id" => ($post->Reply == null || $post->Reply->ReplyToUserID == null ? null : (int)$post->Reply->ReplyToUserID),
                "quote_original_post_id" => ($post->Quote == null || $post->Quote->OriginalPostID == null ? null : (int)$post->Quote->OriginalPostID),
                "quote_original_user_id" => ($post->Quote == null || $post->Quote->OriginalUserID == null ? null : (int)$post->Quote->OriginalUserID),
                "repost_original_post_id" => ($post->Repost == null || $post->Repost->OriginalPostID == null ? null : (int)$post->Repost->OriginalPostID),
                "repost_original_user_id" => ($post->Repost == null || $post->Repost->OriginalUserID == null ? null : (int)$post->Repost->OriginalUserID),
                "flags" => ($post->Flags == null ?  $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])) : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($post->Flags))),
                "is_deleted" => (Converter::hasFlag($post->Flags, PostFlags::Deleted) ? (int)true : (int)false),
                "priority_level" => ($post->PriorityLevel == null ? $this->socialvoidLib->getDatabase()->real_escape_string(PostPriorityLevel::None) : $this->socialvoidLib->getDatabase()->real_escape_string($post->PriorityLevel)),
                "entities" => ($post->Entities == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($post->Entities->toArray()))),
                "likes" => (is_null($post->Likes) ? null : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($post->Likes))),
                "reposts" => (is_null($post->Reposts) ? null : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($post->Reposts))),
                "quotes" => (is_null($post->Quotes) ? null : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($post->Quotes))),
                "replies" => (is_null($post->Replies) ? null : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($post->Replies))),
                "media_content" => (is_null($MediaContent) ? null : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($MediaContent))),
                "last_updated_timestamp" => $post->LastUpdatedTimestamp,
            ], "id", (int)$post->ID);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $this->registerPostCacheEntry($post);
                return $post;
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying to update the post",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Likes an existing post
         *
         * @param int $user_id
         * @param string $post_search_method
         * @param string $post_search_value
         * @param bool $skip_errors
         * @throws CacheException
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @noinspection DuplicatedCode
         */
        public function likePost(int $user_id, string $post_search_method, string $post_search_value, bool $skip_errors=False): void
        {
            try
            {
                $selected_post = $this->getPost($post_search_method, $post_search_value);

                // Do not like the post if it's deleted
                if(Converter::hasFlag($selected_post->Flags, PostFlags::Deleted))
                {
                    throw new PostDeletedException("The requested post was deleted");
                }

                // Like the original post if the requested post is a repost
                if($selected_post->Repost !== null && $selected_post->Repost->OriginalPostID !== null)
                {
                    $selected_post = $this->getPost(PostSearchMethod::ById, $selected_post->Repost->OriginalPostID);

                    // Do not repost the post if it's deleted
                    if(Converter::hasFlag($selected_post->Flags, PostFlags::Deleted))
                    {
                        throw new PostDeletedException("The requested post was deleted");
                    }
                }

                // Do not continue if the user already likes this post
                if(in_array($user_id, $selected_post->Likes))
                    return;

                $this->socialvoidLib->getLikesRecordManager()->likeRecord($user_id, $selected_post->ID);
                $selected_post->Likes[] = $user_id;
                $this->updatePost($selected_post);
            }
            catch(Exception $e)
            {
                if($skip_errors == false) throw $e;
            }
        }

        /**
         * Unlikes an existing post
         *
         * @param int $user_id
         * @param string $post_search_method
         * @param string $post_search_value
         * @param bool $skip_errors
         * @throws CacheException
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         */
        public function unlikePost(int $user_id, string $post_search_method, string $post_search_value, bool $skip_errors=False): void
        {
            try
            {
                $selected_post = $this->getPost($post_search_method, $post_search_value);

                // Do not like the post if it's deleted
                if(Converter::hasFlag($selected_post->Flags, PostFlags::Deleted))
                {
                    throw new PostDeletedException("The requested post was deleted");
                }

                // Like the original post if the requested post is a repost
                if($selected_post->Repost !== null && $selected_post->Repost->OriginalPostID !== null)
                {
                    $selected_post = $this->getPost(PostSearchMethod::ById, $selected_post->Repost->OriginalPostID);

                    // Do not repost the post if it's deleted
                    if(Converter::hasFlag($selected_post->Flags, PostFlags::Deleted))
                    {
                        throw new PostDeletedException("The requested post was deleted");
                    }
                }

                // Do not continue if the user never liked this post
                if(in_array($user_id, $selected_post->Likes) == false)
                    return;

                $this->socialvoidLib->getLikesRecordManager()->unlikeRecord($user_id, $selected_post->ID);
                Converter::removeFlag($selected_post->Likes, $user_id);
                $this->updatePost($selected_post);
            }
            catch(Exception $e)
            {
                if($skip_errors == false) throw $e;
            }
        }

        /**
         * Reposts an existing post
         *
         * @param int $user_id
         * @param string $post_search_method
         * @param string $post_search_value
         * @param int|null $session_id
         * @param string $priority
         * @param array $flags
         * @return Post
         * @throws AlreadyRepostedException
         * @throws CacheException
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @noinspection DuplicatedCode
         */
        public function repostPost(int $user_id, string $post_search_method, string $post_search_value, int $session_id=null, $priority=PostPriorityLevel::None, $flags=[]): Post
        {
            $selected_post = $this->getPost($post_search_method, $post_search_value);

            // Do not repost the post if it's deleted
            if(Converter::hasFlag($selected_post->Flags, PostFlags::Deleted))
            {
                throw new PostDeletedException("The requested post was deleted");
            }

            // Repost the original post if the requested post is a repost
            if($selected_post->Repost !== null && $selected_post->Repost->OriginalPostID !== null)
            {
                $selected_post = $this->getPost(PostSearchMethod::ById, $selected_post->Repost->OriginalPostID);

                // Do not repost the post if it's deleted
                if(Converter::hasFlag($selected_post->Flags, PostFlags::Deleted))
                {
                    throw new PostDeletedException("The requested post was deleted");
                }
            }

            // Check if the post has already been reposted
            try
            {
                $repostRecordState = $this->socialvoidLib->getRepostsRecordManager()->getRecord($user_id, $selected_post->ID);

                if($repostRecordState->PostID !== null)
                {
                    try
                    {
                        $originalRepostPost = $this->getPost(PostSearchMethod::ById, $repostRecordState->PostID);
                        // TODO: Add more details to the AlreadyRepostedException exception.
                        if(Converter::hasFlag($originalRepostPost->Flags, PostFlags::Deleted) == false)
                            throw new AlreadyRepostedException("The requested repost has already been reposted");
                    }
                    catch(PostNotFoundException $e)
                    {
                        // The post wasn't found, so ignore it!
                        unset($e);
                    }
                }
            }
            catch (RepostRecordNotFoundException $e)
            {
                // Ignore this!
                unset($e);
            }

            $timestamp = (int)time();
            $PublicID = BaseIdentification::PostID($user_id, $timestamp, $selected_post->Text);
            $Properties = new Post\Properties();

            $Repost = new Post\Repost();
            $Repost->OriginalPostID = $selected_post->ID;
            $Repost->OriginalUserID = $selected_post->PosterUserID;

            $Query = QueryBuilder::insert_into("posts", [
                "public_id" => $this->socialvoidLib->getDatabase()->real_escape_string($PublicID),
                "session_id" => ($session_id == null ? null : (int)$session_id),
                "poster_user_id" => (int)$user_id,
                "properties" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($Properties->toArray())),
                "repost_original_post_id" => (int)$Repost->OriginalPostID,
                "repost_original_user_id" => (int)$Repost->OriginalUserID,
                "flags" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($flags)),
                "is_deleted" => (int)false,
                "priority_level" => $this->socialvoidLib->getDatabase()->real_escape_string($priority),
                "last_updated_timestamp" => $timestamp,
                "created_timestamp" => $timestamp
            ]);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $returnResults = $this->getPost(PostSearchMethod::ByPublicId, $PublicID);
                $this->registerPostCacheEntry($returnResults);
            }
            else
            {
                throw new DatabaseException("There was an error while trying to repost a post",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }

            $this->socialvoidLib->getRepostsRecordManager()->repostRecord($user_id, $returnResults->ID, $selected_post->ID);
            $selected_post->Reposts[] = $user_id;
            $this->updatePost($selected_post);

            return $returnResults;
        }

        /**
         * Reposts an existing post
         *
         * @param int $user_id
         * @param string $post_search_method
         * @param string $post_search_value
         * @param string $text
         * @param string $source
         * @param int|null $session_id
         * @param array $media
         * @param string $priority
         * @param array $flags
         * @return Post
         * @throws CacheException
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @noinspection DuplicatedCode
         */
        public function quotePost(int $user_id, string $post_search_method, string $post_search_value, string $text, string $source, int $session_id=null, array $media_content=[], $priority=PostPriorityLevel::None, $flags=[]): Post
        {
            $selected_post = $this->getPost($post_search_method, $post_search_value);

            // Do not repost the post if it's deleted
            if(Converter::hasFlag($selected_post->Flags, PostFlags::Deleted))
            {
                throw new PostDeletedException("The requested post was deleted");
            }

            // Quote the original post if the requested post is a repost
            if($selected_post->Repost !== null && $selected_post->Repost->OriginalPostID !== null)
            {
                $selected_post = $this->getPost(PostSearchMethod::ById, $selected_post->Repost->OriginalPostID);

                // Do not repost the post if it's deleted
                if(Converter::hasFlag($selected_post->Flags, PostFlags::Deleted))
                {
                    throw new PostDeletedException("The requested post was deleted");
                }
            }

            $timestamp = (int)time();
            $PublicID = BaseIdentification::PostID($user_id, $timestamp, $selected_post->Text);
            $Properties = new Post\Properties();

            $Quote = new Post\Quote();
            $Quote->OriginalPostID = $selected_post->ID;
            $Quote->OriginalUserID = $selected_post->PosterUserID;

            $textPostParser = new Parser();
            $textPostResults = $textPostParser->parseInput($text);
            if($textPostResults->valid == false)
                throw new InvalidPostTextException("The given post text is invalid", $text);

            $PublicID = BaseIdentification::PostID($user_id, $timestamp, $text);
            $Properties = new Post\Properties();

            // Extract important information from this text
            $Extractor = new Extractor($text);
            $Entities = new Post\Entities();
            $Entities->Hashtags = $Extractor->extractHashtags();
            $Entities->UserMentions = $Extractor->extractMentionedUsernames();
            $Entities->Urls = $Extractor->extractURLs();

            $MediaContentArray = [];
            /** @var Post\MediaContent $value */
            foreach($media_content as $value)
                $MediaContentArray[] = $value->toArray();

            $Query = QueryBuilder::insert_into("posts", [
                "public_id" => $this->socialvoidLib->getDatabase()->real_escape_string($PublicID),
                "text" => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($text)),
                "source" => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($source)),
                "session_id" => ($session_id == null ? null : (int)$session_id),
                "poster_user_id" => (int)$user_id,
                "properties" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($Properties->toArray())),
                "quote_original_post_id" => (int)$Quote->OriginalPostID,
                "quote_original_user_id" => (int)$Quote->OriginalUserID,
                "flags" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($flags)),
                "is_deleted" => (int)false,
                "priority_level" => $this->socialvoidLib->getDatabase()->real_escape_string($priority),
                "entities" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($Entities->toArray())),
                "likes" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "reposts" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "quotes" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "replies" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "media_content" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($MediaContentArray)),
                "last_updated_timestamp" => $timestamp,
                "created_timestamp" => $timestamp
            ]);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $returnResults = $this->getPost(PostSearchMethod::ByPublicId, $PublicID);
                $this->registerPostCacheEntry($returnResults);
            }
            else
            {
                throw new DatabaseException("There was an error while trying to repost a post",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }

            $this->socialvoidLib->getQuotesRecordManager()->quoteRecord($user_id, $returnResults->ID, $selected_post->ID);
            $selected_post->Quotes[] = $returnResults->ID;
            $this->updatePost($selected_post);

            return $returnResults;
        }

        /**
         * Fetches multiple posts from the Database, function is completed faster if
         * BackgroundWorker is enabled
         *
         * @param array $query
         * @param bool $skip_errors
         * @param int $utilization
         * @return Post[]
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws PostNotFoundException
         * @throws BackgroundWorkerNotEnabledException
         * @throws ServiceJobException
         * @throws CacheException
         */
        public function getMultiplePosts(array $query, bool $skip_errors=True, int $utilization=100): array
        {
            if(Utilities::getBoolDefinition("SOCIALVOID_LIB_BACKGROUND_WORKER_ENABLED"))
            {
                return $this->socialvoidLib->getServiceJobManager()->getPostJobs()->resolvePosts(
                    $query, $utilization, $skip_errors
                );
            }
            else
            {
                $return_results = [];

                foreach($query as $value => $search_method)
                {
                    try
                    {
                        $return_results[] = $this->getPost($search_method, $value);
                    }
                    catch(Exception $e)
                    {
                        if($skip_errors == false) throw $e;
                    }
                }

                return $return_results;
            }
        }

        /**
         * Registers a user cache entry
         *
         * @param Post $post
         * @throws CacheException
         */
        private function registerPostCacheEntry(Post $post): void
        {
            if($this->socialvoidLib->getRedisBasicCacheConfiguration()["Enabled"])
            {
                $CacheEntryInput = new RegisterCacheInput();
                $CacheEntryInput->ObjectType = CacheEntryObjectType::Post;
                $CacheEntryInput->ObjectData = $post->toArray();
                $CacheEntryInput->Pointers = [$post->ID, $post->PublicID];

                try
                {
                    $this->socialvoidLib->getBasicRedisCacheManager()->registerCache(
                        $CacheEntryInput,
                        $this->socialvoidLib->getRedisBasicCacheConfiguration()["PostCacheTTL"],
                        $this->socialvoidLib->getRedisBasicCacheConfiguration()["PostCacheLimit"]
                    );
                }
                catch(Exception $e)
                {
                    throw new CacheException("There was an error while trying to register the post cache entry", 0, $e);
                }
            }
        }

        /**
         * Gets a post cache entry, returns null if it's a miss
         *
         * @param string $value
         * @return Post|null
         * @throws CacheException
         */
        private function getPostCacheEntry(string $value): ?Post
        {
            if($this->socialvoidLib->getRedisBasicCacheConfiguration()["Enabled"] == false)
                throw new CacheException("BasicRedisCache is not enabled");

            try
            {
                $CacheEntryResults = $this->socialvoidLib->getBasicRedisCacheManager()->getCacheEntry(
                    CacheEntryObjectType::Post, $value);
            }
            catch (CacheMissedException $e)
            {
                return null;
            }
            catch (DependencyError | RedisCacheException $e)
            {
                throw new CacheException("There was an issue while trying to request a post cache entry", 0, $e);
            }

            return Post::fromArray($CacheEntryResults->ObjectData);
        }
    }
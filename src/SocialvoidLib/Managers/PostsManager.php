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

    use BackgroundWorker\Exceptions\ServerNotReachableException;
    use Exception;
    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\Flags\PostFlags;
    use SocialvoidLib\Abstracts\Levels\PostPriorityLevel;
    use SocialvoidLib\Abstracts\Options\ParseOptions;
    use SocialvoidLib\Abstracts\SearchMethods\PostSearchMethod;
    use SocialvoidLib\Abstracts\Types\CacheEntryObjectType;
    use SocialvoidLib\Classes\Converter;
    use SocialvoidLib\Classes\PostText\TwitterMethod\Parser;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Exceptions\GenericInternal\BackgroundWorkerNotEnabledException;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\CacheMissedException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\DependencyError;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSlaveHashException;
    use SocialvoidLib\Exceptions\GenericInternal\RedisCacheException;
    use SocialvoidLib\Exceptions\GenericInternal\ServiceJobException;
    use SocialvoidLib\Exceptions\GenericInternal\UserHasInvalidSlaveHashException;
    use SocialvoidLib\Exceptions\Internal\LikeRecordNotFoundException;
    use SocialvoidLib\Exceptions\Internal\QuoteRecordNotFoundException;
    use SocialvoidLib\Exceptions\Internal\ReplyRecordNotFoundException;
    use SocialvoidLib\Exceptions\Internal\RepostRecordNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\AlreadyRepostedException;
    use SocialvoidLib\Exceptions\Standard\Network\PostDeletedException;
    use SocialvoidLib\Exceptions\Standard\Network\PostNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPostTextException;
    use SocialvoidLib\InputTypes\RegisterCacheInput;
    use SocialvoidLib\Objects\Post;
    use SocialvoidLib\Objects\User;
    use SocialvoidLib\SocialvoidLib;
    use Symfony\Component\Uid\Uuid;
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
         * @param User $user
         * @param string $source
         * @param string $text
         * @param string|null $session_id
         * @param array $media_content
         * @param string $priority
         * @param array $flags
         * @return Post
         * @throws CacheException
         * @throws DatabaseException
         * @throws InvalidPostTextException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws PostNotFoundException
         * @noinspection DuplicatedCode
         * @noinspection PhpBooleanCanBeSimplifiedInspection
         */
        public function publishPost(User $user, string $source, string $text, string $session_id=null, array $media_content=[], string $priority=PostPriorityLevel::None, array $flags=[]): Post
        {
            $timestamp = time();

            $textPostParser = new Parser();
            $textPostResults = $textPostParser->parseInput($text);
            if($textPostResults->valid == false)
                throw new InvalidPostTextException('The given post text is invalid', $text);

            $PublicID = Uuid::v1();
            $Properties = new Post\Properties();

            // Extract important information from this text
            $Entities = Utilities::extractEntities($text, [
                ParseOptions::Hashtags,
                ParseOptions::URLs,
                ParseOptions::Mentions
            ]);

            $MediaContentArray = [];
            /** @var Post\MediaContent $value */
            foreach($media_content as $value)
                $MediaContentArray[] = $value->toArray();

            $EntitiesArray = [];
            foreach($Entities as $textEntity)
                $EntitiesArray[] = $textEntity->toArray();

            $Query = QueryBuilder::insert_into('posts', [
                'public_id' => $this->socialvoidLib->getDatabase()->real_escape_string($PublicID),
                'text' => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($text)),
                'source' => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($source)),
                'session_id' => ($session_id == null ? null : $session_id),
                'poster_user_id' => $user->ID,
                'properties' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($Properties->toArray())),
                'flags' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($flags)),
                'is_deleted' => (int)false,
                'priority_level' => $this->socialvoidLib->getDatabase()->real_escape_string($priority),
                'text_entities' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($EntitiesArray)),
                'media_content' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($MediaContentArray)),
                'count_last_updated_timestamp' => $timestamp,
                'last_updated_timestamp' => $timestamp,
                'created_timestamp' => $timestamp
            ]);

            $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer($user->SlaveServer);
            $QueryResults = $SelectedSlave->getConnection()->query($Query);
            if($QueryResults)
            {
                $returnResults = $this->getPost(PostSearchMethod::ByPublicId, $SelectedSlave->MysqlServerPointer->HashPointer . '-' . $PublicID);
                $this->registerPostCacheEntry($returnResults);
                return $returnResults;
            }
            else
            {
                throw new DatabaseException('There was an error while trying to create a post',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
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
         * @throws InvalidSlaveHashException
         */
        public function getPost(string $search_method, string $value): Post
        {
            switch($search_method)
            {
                case PostSearchMethod::ByPublicId:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = $this->socialvoidLib->getDatabase()->real_escape_string($value);
                    break;

                /** @noinspection PhpDeprecationInspection */
                case PostSearchMethod::ById:
                    throw new InvalidSearchMethodException('The use of ById is no longer available, use ByPublicId instead (https://github.com/intellivoid/SocialvoidLib/issues/1)', $search_method, $value);

                default:
                    throw new InvalidSearchMethodException('The given search method is invalid for getPost()', $search_method, $value);
            }

            if($this->socialvoidLib->getRedisBasicCacheConfiguration()['Enabled'])
            {
                $CachedPost = $this->getPostCacheEntry($value);
                if($CachedPost !== null) return $CachedPost;
            }

            $Query = QueryBuilder::select('posts', [
                'public_id',
                'text',
                'source',
                'properties',
                'session_id',
                'poster_user_id',
                'reply_to_post_id',
                'reply_to_user_id',
                'quote_original_post_id',
                'quote_original_user_id',
                'repost_original_post_id',
                'repost_original_user_id',
                'original_thread_post_id',
                'flags',
                'priority_level',
                'text_entities',
                'like_count',
                'repost_count',
                'quote_count',
                'reply_count',
                'media_content',
                'count_last_updated_timestamp',
                'last_updated_timestamp',
                'created_timestamp'
            ], $search_method, Utilities::removeSlaveHash($value), null, null, 1);

            $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer(Utilities::getSlaveHash($value));
            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new PostNotFoundException('The requested post was not found');
                }
                else
                {
                    $Row['properties'] = ($Row['properties'] == null ? null : ZiProto::decode($Row['properties']));
                    $Row['flags'] = ($Row['flags'] == null ? null : ZiProto::decode($Row['flags']));
                    $Row['text_entities'] = ($Row['text_entities'] == null ? null : ZiProto::decode($Row['text_entities']));
                    $Row['media_content'] = ($Row['media_content'] == null ? null : ZiProto::decode($Row['media_content']));
                    $Row['text'] = ($Row['text'] == null ? null : urldecode($Row['text']));
                    $Row['source'] = ($Row['source'] == null ? null : urldecode($Row['source']));

                    $returnResults = Post::fromAlternativeArray($Row);
                    $returnResults->PublicID = $SelectedSlave->MysqlServerPointer->HashPointer . '-' . $returnResults->PublicID;

                    // Update counts if it's older than an hour
                    if((time() - $returnResults->CountLastUpdatedTimestamp) > 3600 && $returnResults->Repost == null)
                    {
                        $returnResults->LikeCount = $this->socialvoidLib->getLikesRecordManager()->getLikesCount($returnResults->PublicID);
                        $returnResults->QuoteCount = $this->socialvoidLib->getQuotesRecordManager()->getQuotesCount($returnResults->PublicID);
                        $returnResults->RepostCount = $this->socialvoidLib->getRepostsRecordManager()->getRepostsCount($returnResults->PublicID);
                        $returnResults->ReplyCount = $this->socialvoidLib->getReplyRecordManager()->getRepliesCount($returnResults->PublicID);
                        $returnResults->CountLastUpdatedTimestamp = time();
                        $returnResults = $this->updatePost($returnResults);
                    }

                    $this->registerPostCacheEntry($returnResults);
                    return $returnResults;
                }
            }
            else
            {
                throw new DatabaseException(
                    'There was an error while trying retrieve an existing post from the network',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
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
         * @throws InvalidSlaveHashException
         * @noinspection PhpBooleanCanBeSimplifiedInspection
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function updatePost(Post $post): Post
        {
            $MediaContent = [];
            $TextEntities = [];

            foreach($post->TextEntities as $textEntity)
                $TextEntities[] = $textEntity->toArray();

            if($post->MediaContent !== null)
            {
                foreach($post->MediaContent as $mediaContent)
                    $MediaContent[] = $mediaContent->toArray();
            }

            $post->LastUpdatedTimestamp = time();
            
            // TODO: Validate text
            // Probably the most CPU intensive update there is here.
            $Query = QueryBuilder::update('posts', [
                'text' => ($post->Text == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($post->Text))),
                'source' => ($post->Source == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($post->Source))),
                'properties' => ($post->Properties == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($post->Properties->toArray()))),
                'poster_user_id' => ($post->PosterUserID == null ? null : (int)$post->PosterUserID),
                'reply_to_post_id' => ($post->Reply == null || $post->Reply->ReplyToPostID == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string($post->Reply->ReplyToPostID)),
                'reply_to_user_id' => ($post->Reply == null || $post->Reply->ReplyToUserID == null ? null : (int)$post->Reply->ReplyToUserID),
                'quote_original_post_id' => ($post->Quote == null || $post->Quote->OriginalPostID == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string($post->Quote->OriginalPostID)),
                'quote_original_user_id' => ($post->Quote == null || $post->Quote->OriginalUserID == null ? null : (int)$post->Quote->OriginalUserID),
                'repost_original_post_id' => ($post->Repost == null || $post->Repost->OriginalPostID == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string($post->Repost->OriginalPostID)),
                'repost_original_user_id' => ($post->Repost == null || $post->Repost->OriginalUserID == null ? null : (int)$post->Repost->OriginalUserID),
                'original_thread_post_id' => ($post->OriginalPostThreadID == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string($post->OriginalPostThreadID)),
                'flags' => ($post->Flags == null ?  $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])) : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($post->Flags))),
                'is_deleted' => (Converter::hasFlag($post->Flags, PostFlags::Deleted) ? (int)true : (int)false),
                'priority_level' => ($post->PriorityLevel == null ? $this->socialvoidLib->getDatabase()->real_escape_string(PostPriorityLevel::None) : $this->socialvoidLib->getDatabase()->real_escape_string($post->PriorityLevel)),
                'text_entities' => ($post->TextEntities == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($TextEntities))),
                'like_count' => ($post->LikeCount == null ? 0 : (int)$post->LikeCount),
                'repost_count' => ($post->RepostCount == null ? 0 : (int)$post->RepostCount),
                'quote_count' => ($post->QuoteCount == null ? 0 : (int)$post->QuoteCount),
                'reply_count' => ($post->ReplyCount == null ? 0 : (int)$post->ReplyCount),
                'media_content' => (is_null($MediaContent) ? null : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($MediaContent))),
                'last_updated_timestamp' => (int)$post->LastUpdatedTimestamp,
                'count_last_updated_timestamp' => (int)$post->CountLastUpdatedTimestamp
            ], 'public_id', $this->socialvoidLib->getDatabase()->real_escape_string(Utilities::removeSlaveHash($post->PublicID)));

            $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer(Utilities::getSlaveHash($post->PublicID));
            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            if($QueryResults)
            {
                $this->registerPostCacheEntry($post);
                return $post;
            }
            else
            {
                throw new DatabaseException(
                    'There was an error while trying to update the post',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
        }

        /**
         * Likes an existing post
         *
         * @param User $user
         * @param Post $post
         * @param bool $skip_errors
         * @throws CacheException
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws LikeRecordNotFoundException
         * @noinspection DuplicatedCode
         */
        public function likePost(User $user, Post $post, bool $skip_errors=False): void
        {
            try
            {
                // Do not like the post if it's deleted
                if(Converter::hasFlag($post->Flags, PostFlags::Deleted))
                {
                    throw new PostDeletedException('The requested post was deleted');
                }

                // Like the original post if the requested post is a repost
                if($post->Repost !== null && $post->Repost->OriginalPostID !== null)
                {
                    $post = $this->getPost(PostSearchMethod::ByPublicId, $post->Repost->OriginalPostID);

                    // Do not repost the post if it's deleted
                    if(Converter::hasFlag($post->Flags, PostFlags::Deleted))
                    {
                        throw new PostDeletedException('The requested post was deleted');
                    }
                }

                $this->socialvoidLib->getLikesRecordManager()->likeRecord($user->ID, $post->PublicID);
                $post->LikeCount += 1;
                if($post->LikeCount < 0)
                    $post->LikeCount = 0;
                $this->updatePost($post);
            }
            catch(Exception $e)
            {
                if($skip_errors == false) throw $e;
            }
        }

        /**
         * Unlikes an existing post
         *
         * @param User $user
         * @param Post $post
         * @param bool $skip_errors
         * @throws CacheException
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws LikeRecordNotFoundException
         * @noinspection DuplicatedCode
         */
        public function unlikePost(User $user, Post $post, bool $skip_errors=False): void
        {
            try
            {
                // Do not like the post if it's deleted
                if(Converter::hasFlag($post->Flags, PostFlags::Deleted))
                {
                    throw new PostDeletedException('The requested post was deleted');
                }

                // Like the original post if the requested post is a repost
                if($post->Repost !== null && $post->Repost->OriginalPostID !== null)
                {
                    $post = $this->getPost(PostSearchMethod::ByPublicId, $post->Repost->OriginalPostID);

                    // Do not repost the post if it's deleted
                    if(Converter::hasFlag($post->Flags, PostFlags::Deleted))
                    {
                        throw new PostDeletedException('The requested post was deleted');
                    }
                }

                $this->socialvoidLib->getLikesRecordManager()->unlikeRecord($user->ID, $post->PublicID);
                $post->LikeCount -= 1;
                if($post->LikeCount < 0)
                    $post->LikeCount = 0;
                $this->updatePost($post);
            }
            catch(Exception $e)
            {
                if($skip_errors == false) throw $e;
            }
        }

        /**
         * Reposts an existing post
         *
         * @param User $user
         * @param Post $post
         * @param string|null $session_id
         * @param string $priority
         * @param array $flags
         * @return Post
         * @throws AlreadyRepostedException
         * @throws CacheException
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws RepostRecordNotFoundException
         * @throws UserHasInvalidSlaveHashException
         * @noinspection DuplicatedCode
         * @noinspection PhpBooleanCanBeSimplifiedInspection
         */
        public function repostPost(User $user, Post $post, string $session_id, string $priority=PostPriorityLevel::None, array $flags=[]): Post
        {
            // Do not repost the post if it's deleted
            if(Converter::hasFlag($post->Flags, PostFlags::Deleted))
            {
                throw new PostDeletedException('The requested post was deleted');
            }

            // Repost the original post if the requested post is a repost
            if($post->Repost !== null && $post->Repost->OriginalPostID !== null)
            {
                $post = $this->getPost(PostSearchMethod::ByPublicId, $post->Repost->OriginalPostID);

                // Do not repost the post if it's deleted
                if(Converter::hasFlag($post->Flags, PostFlags::Deleted))
                {
                    throw new PostDeletedException('The requested post was deleted');
                }
            }

            $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer($user->SlaveServer);

            // Check if the post has already been reposted
            try
            {
                $repostRecordState = $this->socialvoidLib->getRepostsRecordManager()->getRepostedRecord($user->ID, $post->PublicID);
                if($repostRecordState->Reposted)
                {
                    try
                    {
                        $originalRepostPost = $this->getPost(PostSearchMethod::ByPublicId, $repostRecordState->PostID);
                        if(Converter::hasFlag($originalRepostPost->Flags, PostFlags::Deleted) == false)
                            throw new AlreadyRepostedException('The requested repost has already been reposted');
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

            $timestamp = time();
            $PublicID = Uuid::v1();
            $Properties = new Post\Properties();

            $Repost = new Post\Repost();
            $Repost->OriginalPostID = $post->PublicID;
            $Repost->OriginalUserID = $post->PosterUserID;

            $Query = QueryBuilder::insert_into('posts', [
                'public_id' => $this->socialvoidLib->getDatabase()->real_escape_string($PublicID),
                'session_id' => $session_id,
                'poster_user_id' => $user->ID,
                'properties' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($Properties->toArray())),
                'repost_original_post_id' => $Repost->OriginalPostID,
                'repost_original_user_id' => (int)$Repost->OriginalUserID,
                'flags' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($flags)),
                'is_deleted' => (int)false,
                'priority_level' => $this->socialvoidLib->getDatabase()->real_escape_string($priority),
                'last_updated_timestamp' => $timestamp,
                'count_last_updated_timestamp' => $timestamp,
                'created_timestamp' => $timestamp
            ]);
            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            if($QueryResults)
            {
                $returnResults = $this->getPost(PostSearchMethod::ByPublicId, $SelectedSlave->MysqlServerPointer->HashPointer . '-' . $PublicID);
                $this->registerPostCacheEntry($returnResults);
            }
            else
            {
                throw new DatabaseException('There was an error while trying to repost a post',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }

            $this->socialvoidLib->getRepostsRecordManager()->repostRecord($user->ID, $returnResults->PublicID, $post->PublicID);
            $post->RepostCount += 1;
            $this->updatePost($post);

            return $returnResults;
        }

        /**
         * Reposts an existing post
         *
         * @param User $user
         * @param Post $post
         * @param string $text
         * @param string $source
         * @param string|null $session_id
         * @param array $media_content
         * @param string $priority
         * @param array $flags
         * @return Post
         * @throws CacheException
         * @throws DatabaseException
         * @throws InvalidPostTextException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws QuoteRecordNotFoundException
         * @noinspection DuplicatedCode
         * @noinspection PhpBooleanCanBeSimplifiedInspection
         */
        public function quotePost(User $user, Post $post, string $text, string $source, string $session_id=null, array $media_content=[], string $priority=PostPriorityLevel::None, array $flags=[]): Post
        {
            // Do not quote the post if it's deleted
            if(Converter::hasFlag($post->Flags, PostFlags::Deleted))
            {
                throw new PostDeletedException('The requested post was deleted');
            }

            // Quote the original post if the requested post is a repost
            if($post->Repost !== null && $post->Repost->OriginalPostID !== null)
            {
                $post = $this->getPost(PostSearchMethod::ByPublicId, $post->Repost->OriginalPostID);

                // Do not repost the post if it's deleted
                if(Converter::hasFlag($post->Flags, PostFlags::Deleted))
                {
                    throw new PostDeletedException('The requested post was deleted');
                }
            }

            $timestamp = time();

            $Quote = new Post\Quote();
            $Quote->OriginalPostID = $post->PublicID;
            $Quote->OriginalUserID = $post->PosterUserID;

            $textPostParser = new Parser();
            $textPostResults = $textPostParser->parseInput($text);
            if($textPostResults->valid == false)
                throw new InvalidPostTextException('The given post text is invalid', $text);

            $PublicID = Uuid::v1();
            $Properties = new Post\Properties();

            // Extract important information from this text
            $Entities = Utilities::extractEntities($text, [
                ParseOptions::Hashtags,
                ParseOptions::URLs,
                ParseOptions::Mentions
            ]);

            $EntitiesArray = [];
            foreach($Entities as $textEntity)
                $EntitiesArray[] = $textEntity->toArray();

            $MediaContentArray = [];
            /** @var Post\MediaContent $value */
            foreach($media_content as $value)
                $MediaContentArray[] = $value->toArray();

            $Query = QueryBuilder::insert_into('posts', [
                'public_id' => $this->socialvoidLib->getDatabase()->real_escape_string($PublicID),
                'text' => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($text)),
                'source' => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($source)),
                'session_id' => ($session_id == null ? null : $session_id),
                'poster_user_id' => $user->ID,
                'properties' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($Properties->toArray())),
                'quote_original_post_id' => $Quote->OriginalPostID,
                'quote_original_user_id' => (int)$Quote->OriginalUserID,
                'flags' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($flags)),
                'is_deleted' => (int)false,
                'priority_level' => $this->socialvoidLib->getDatabase()->real_escape_string($priority),
                'text_entities' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($EntitiesArray)),
                'media_content' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($MediaContentArray)),
                'count_last_updated_timestamp' => $timestamp,
                'last_updated_timestamp' => $timestamp,
                'created_timestamp' => $timestamp
            ]);

            $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer($user->SlaveServer);
            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            if($QueryResults)
            {
                $returnResults = $this->getPost(PostSearchMethod::ByPublicId, $SelectedSlave->MysqlServerPointer->HashPointer . '-' . $PublicID);
                $this->registerPostCacheEntry($returnResults);
            }
            else
            {
                throw new DatabaseException('There was an error while trying to repost a post',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }

            $this->socialvoidLib->getQuotesRecordManager()->quoteRecord($user->ID, $returnResults->PublicID, $post->PublicID);
            $post->QuoteCount += 1;
            $this->updatePost($post);

            return $returnResults;
        }

        /**
         * @param User $user
         * @param Post $post
         * @param string $text
         * @param string $source
         * @param string|null $session_id
         * @param array $media_content
         * @param string $priority
         * @param array $flags
         * @return Post
         * @throws CacheException
         * @throws DatabaseException
         * @throws InvalidPostTextException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws ReplyRecordNotFoundException
         * @noinspection DuplicatedCode
         * @noinspection PhpBooleanCanBeSimplifiedInspection
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function replyToPost(User $user, Post $post, string $text, string $source, string $session_id=null, array $media_content=[], string $priority=PostPriorityLevel::None, array $flags=[]): Post
        {
            // Do not repost the post if it's deleted
            if(Converter::hasFlag($post->Flags, PostFlags::Deleted))
            {
                throw new PostDeletedException('The requested post was deleted');
            }

            // Quote the original post if the requested post is a repost
            if($post->Repost !== null && $post->Repost->OriginalPostID !== null)
            {
                $post = $this->getPost(PostSearchMethod::ByPublicId, $post->Repost->OriginalPostID);

                if(Converter::hasFlag($post->Flags, PostFlags::Deleted))
                {
                    throw new PostDeletedException('The requested post was deleted');
                }
            }

            $original_thread_post_id = $post->PublicID;
            if($post->OriginalPostThreadID !== null)
                $original_thread_post_id = $post->OriginalPostThreadID;

            $timestamp = time();

            $Reply = new Post\Reply();
            $Reply->ReplyToPostID = $post->PublicID;
            $Reply->ReplyToUserID = $post->PosterUserID;

            $textPostParser = new Parser();
            $textPostResults = $textPostParser->parseInput($text);
            if($textPostResults->valid == false)
                throw new InvalidPostTextException('The given post text is invalid', $text);

            $PublicID = Uuid::v1();
            $Properties = new Post\Properties();

            // Extract important information from this text
            $Entities = Utilities::extractEntities($text, [
                ParseOptions::Hashtags,
                ParseOptions::URLs,
                ParseOptions::Mentions
            ]);

            $EntitiesArray = [];
            foreach($Entities as $textEntity)
                $EntitiesArray[] = $textEntity->toArray();

            $MediaContentArray = [];
            /** @var Post\MediaContent $value */
            foreach($media_content as $value)
                $MediaContentArray[] = $value->toArray();

            $Query = QueryBuilder::insert_into('posts', [
                'public_id' => $this->socialvoidLib->getDatabase()->real_escape_string($PublicID),
                'text' => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($text)),
                'source' => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($source)),
                'session_id' => ($session_id == null ? null : $session_id),
                'poster_user_id' => (int)$user->ID,
                'properties' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($Properties->toArray())),
                'reply_to_post_id' => $this->socialvoidLib->getDatabase()->real_escape_string($Reply->ReplyToPostID),
                'reply_to_user_id' => (int)$Reply->ReplyToUserID,
                'original_thread_post_id' => $this->socialvoidLib->getDatabase()->real_escape_string($original_thread_post_id),
                'flags' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($flags)),
                'is_deleted' => (int)false,
                'priority_level' => $this->socialvoidLib->getDatabase()->real_escape_string($priority),
                'text_entities' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($EntitiesArray)),
                'media_content' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($MediaContentArray)),
                'count_last_updated_timestamp' => $timestamp,
                'last_updated_timestamp' => $timestamp,
                'created_timestamp' => $timestamp
            ]);

            $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer($user->SlaveServer);
            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            if($QueryResults)
            {
                $returnResults = $this->getPost(PostSearchMethod::ByPublicId, $SelectedSlave->MysqlServerPointer->HashPointer . '-' . $PublicID);
                $this->registerPostCacheEntry($returnResults);
            }
            else
            {
                throw new DatabaseException('There was an error while trying to repost a post',
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }

            $this->socialvoidLib->getReplyRecordManager()->replyRecord($user->ID, $returnResults->PublicID, $post->PublicID);
            $post->ReplyCount += 1;
            $this->updatePost($post);

            return $returnResults;
        }

        /**
         * Marks a post as deleted, cannot be reverted.
         *
         * @param Post $post
         * @throws CacheException
         * @throws DatabaseException
         * @throws InvalidSlaveHashException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws RepostRecordNotFoundException
         * @throws QuoteRecordNotFoundException
         * @throws ReplyRecordNotFoundException
         */
        public function deletePost(Post $post)
        {
            // Do not delete the post if it's deleted
            if(Converter::hasFlag($post->Flags, PostFlags::Deleted))
            {
                throw new PostDeletedException('The requested post was deleted');
            }

            // Add the deleted flag to the post
            Converter::addFlag($post->Flags, PostFlags::Deleted);
            $this->updatePost($post);

            // Remove this post from its associates
            if($post->Reply !== null && $post->Reply->ReplyToPostID !== null)
            {
                $this->socialvoidLib->getReplyRecordManager()->unreplyRecord(
                    $post->PosterUserID, $post->Reply->ReplyToPostID, $post->PublicID
                );
            }
            if($post->Quote !== null && $post->Quote->OriginalPostID !== null)
            {
                $this->socialvoidLib->getQuotesRecordManager()->unquoteRecord(
                    $post->PosterUserID, $post->PublicID, $post->Quote->OriginalPostID
                );
            }
            if($post->Repost !== null && $post->Repost->OriginalPostID !== null)
            {
                $this->socialvoidLib->getRepostsRecordManager()->unrepostRecord(
                    $post->PosterUserID, $post->PublicID, $post->Repost->OriginalPostID
                );
            }
        }

        /**
         * Fetches multiple posts from the Database, function is completed faster if
         * BackgroundWorker is enabled
         *
         * @param array $query
         * @param bool $skip_errors
         * @param int $utilization
         * @return Post[]
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws PostNotFoundException
         * @throws ServiceJobException
         * @throws ServerNotReachableException
         */
        public function getMultiplePosts(array $query, bool $skip_errors=True, int $utilization=15): array
        {
            if(Utilities::getBoolDefinition('SOCIALVOID_LIB_BACKGROUND_WORKER_ENABLED'))
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
            // TODO: Add check if post cache is enabled
            if($this->socialvoidLib->getRedisBasicCacheConfiguration()['Enabled'])
            {
                $CacheEntryInput = new RegisterCacheInput();
                $CacheEntryInput->ObjectType = CacheEntryObjectType::Post;
                $CacheEntryInput->ObjectData = $post->toArray();
                $CacheEntryInput->Pointers = [$post->PublicID];

                try
                {
                    $this->socialvoidLib->getBasicRedisCacheManager()->registerCache(
                        $CacheEntryInput,
                        $this->socialvoidLib->getRedisBasicCacheConfiguration()['PostCacheTTL'],
                        $this->socialvoidLib->getRedisBasicCacheConfiguration()['PostCacheLimit']
                    );
                }
                catch(Exception $e)
                {
                    throw new CacheException('There was an error while trying to register the post cache entry', 0, $e);
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
            // TODO: Add check if post cache is enabled
            if($this->socialvoidLib->getRedisBasicCacheConfiguration()['Enabled'] == false)
                throw new CacheException('BasicRedisCache is not enabled');

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
                throw new CacheException('There was an issue while trying to request a post cache entry', 0, $e);
            }

            return Post::fromArray($CacheEntryResults->ObjectData);
        }
    }
<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Network;

    use BackgroundWorker\Exceptions\ServerNotReachableException;
    use InvalidArgumentException;
    use SocialvoidLib\Abstracts\Flags\PostFlags;
    use SocialvoidLib\Abstracts\Levels\PostPriorityLevel;
    use SocialvoidLib\Abstracts\SearchMethods\PostSearchMethod;
    use SocialvoidLib\Abstracts\SearchMethods\TimelineSearchMethod;
    use SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod;
    use SocialvoidLib\Abstracts\Types\Standard\PostType;
    use SocialvoidLib\Abstracts\Types\Standard\TextEntityType;
    use SocialvoidLib\Classes\Converter;
    use SocialvoidLib\Exceptions\GenericInternal\BackgroundWorkerNotEnabledException;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\DependencyError;
    use SocialvoidLib\Exceptions\GenericInternal\DisplayPictureException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSlaveHashException;
    use SocialvoidLib\Exceptions\GenericInternal\RedisCacheException;
    use SocialvoidLib\Exceptions\GenericInternal\ServiceJobException;
    use SocialvoidLib\Exceptions\GenericInternal\UserHasInvalidSlaveHashException;
    use SocialvoidLib\Exceptions\Internal\LikeRecordNotFoundException;
    use SocialvoidLib\Exceptions\Internal\QuoteRecordNotFoundException;
    use SocialvoidLib\Exceptions\Internal\ReplyRecordNotFoundException;
    use SocialvoidLib\Exceptions\Internal\RepostRecordNotFoundException;
    use SocialvoidLib\Exceptions\Internal\UserTimelineNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Network\AccessDeniedException;
    use SocialvoidLib\Exceptions\Standard\Network\AlreadyRepostedException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PostDeletedException;
    use SocialvoidLib\Exceptions\Standard\Network\PostNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPageValueException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPostTextException;
    use SocialvoidLib\Exceptions\Standard\Validation\TooManyAttachmentsException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Cursor;
    use SocialvoidLib\Objects\Post;
    use SocialvoidLib\Objects\Standard\Document;
    use SocialvoidLib\Objects\Standard\Peer;
    use SocialvoidLib\Objects\Standard\TimelineState;
    use SocialvoidLib\Objects\User;
    use Zimage\Exceptions\CannotGetOriginalImageException;
    use Zimage\Exceptions\FileNotFoundException;
    use Zimage\Exceptions\InvalidZimageFileException;
    use Zimage\Exceptions\SizeNotSetException;
    use Zimage\Exceptions\UnsupportedImageTypeException;

    /**
     * Class Timeline
     * @package SocialvoidLib\Network
     */
    class Timeline
    {
        /**
         * @var NetworkSession
         */
        private NetworkSession $networkSession;

        /**
         * Timeline constructor.
         * @param NetworkSession $networkSession
         */
        public function __construct(NetworkSession $networkSession)
        {
            $this->networkSession = $networkSession;
        }

        /**
         * Distributes a new post to the timeline, and it's users
         *
         * @param string $text
         * @param array $attachments
         * @param array $flags
         * @return Post
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DependencyError
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws InvalidPostTextException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws PostNotFoundException
         * @throws RedisCacheException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @throws TooManyAttachmentsException
         * @throws UserHasInvalidSlaveHashException
         * @throws UserTimelineNotFoundException
         */
        public function compose(string $text, array $attachments=[], array $flags=[]): Post
        {
            if ($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $PostObject = $this->networkSession->getSocialvoidLib()->getPostsManager()->publishPost(
                $this->networkSession->getAuthenticatedUser(),
                Converter::getSource($this->networkSession->getActiveSession()),
                $text, $this->networkSession->getActiveSession()->ID, $attachments,
                PostPriorityLevel::High, $flags
            );

            $this->networkSession->getSocialvoidLib()->getTimelineManager()->distributePost(
                $this->networkSession->getAuthenticatedUser(), $PostObject->PublicID,15, true
            );

            return $PostObject;
        }

        /**
         * Retrieves the raw post object from the database
         *
         * @param string $post_id
         * @return Post
         * @throws CacheException
         * @throws DatabaseException
         * @throws DependencyError
         * @throws DocumentNotFoundException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws NotAuthenticatedException
         * @throws PostNotFoundException
         * @throws RedisCacheException
         * @throws TooManyAttachmentsException
         */
        public function getPost(string $post_id): Post
        {
            if ($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            return $this->networkSession->getSocialvoidLib()->getPostsManager()->getPost(
                PostSearchMethod::ByPublicId, $post_id
            );
        }

        /**
         * Returns flags related to the authenticated peer and the post
         *
         * @param \SocialvoidLib\Objects\Standard\Post $post
         * @return array
         * @throws DatabaseException
         * @throws PostNotFoundException
         */
        public function getRelationalPostFlags(\SocialvoidLib\Objects\Standard\Post $post): array
        {
            $Flags = $post->Flags;

            if(in_array(PostFlags::Deleted, $Flags) == false)
            {
                try
                {
                    if ($this->networkSession->getSocialvoidLib()->getLikesRecordManager()->getRecord(
                        $this->networkSession->getAuthenticatedUser()->ID, $post->ID)->Liked)
                    {
                        $Flags[] = PostFlags::Liked;
                    }
                }
                catch (LikeRecordNotFoundException $e)
                {
                    unset($e);
                }

                try
                {
                    if($this->networkSession->getSocialvoidLib()->getRepostsRecordManager()->getRecord(
                        $this->networkSession->getAuthenticatedUser()->ID, $post->ID)->Reposted
                    )
                    {
                        $Flags[] = PostFlags::Reposted;
                    }
                }
                catch(RepostRecordNotFoundException $e)
                {
                    unset($e);
                }
            }

            return array_unique($Flags);
        }

        /**
         * Retrieves a standard post object while resolving all of it's contents
         *
         * @param string|Post $post
         * @param bool $first_layer
         * @return \SocialvoidLib\Objects\Standard\Post
         * @throws AccessDeniedException
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws CannotGetOriginalImageException
         * @throws DatabaseException
         * @throws DependencyError
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws FileNotFoundException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws InvalidZimageFileException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws PostNotFoundException
         * @throws RedisCacheException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @throws SizeNotSetException
         * @throws TooManyAttachmentsException
         * @throws UnsupportedImageTypeException
         * @noinspection DuplicatedCode
         */
        public function getStandardPost($post, bool $first_layer=true): \SocialvoidLib\Objects\Standard\Post
        {
            // Check if authenticated
            if ($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            if(gettype($post) == 'string')
            {
                $post = $this->networkSession->getSocialvoidLib()->getPostsManager()->getPost(
                    PostSearchMethod::ByPublicId, $post
                );
            }
            elseif(is_object($post) == false && get_class($post) !== Post::class)
            {
                throw new InvalidArgumentException('Argument \'post\' must be type \'' . Post::class . '\' or Post ID string');
            }

            // Disabled for now due to relational flags
            /**
            if(
                $this->networkSession->getSocialvoidLib()->getRedisBasicCacheConfiguration()['Enabled'] &&
                $this->networkSession->getSocialvoidLib()->getRedisBasicCacheConfiguration()['StandardPostCacheEnabled']
            )
            {
                try
                {
                    $CacheEntryResults = $this->networkSession->getSocialvoidLib()->getBasicRedisCacheManager()->getCacheEntry(
                        CacheEntryObjectType::StandardPost, $post->PublicID);

                    $StandardPost = \SocialvoidLib\Objects\Standard\Post::fromArray($CacheEntryResults->ObjectData);
                }
                catch(CacheMissedException $e)
                {
                    unset($e);
                }
                catch (DependencyError | RedisCacheException $e)
                {
                    throw new CacheException('There was an issue while trying to request a standard post cache entry', 0, $e);
                }
            }
             */

            // Return the post as is if it's deleted
            if(Converter::hasFlag($post->Flags, PostFlags::Deleted))
                return \SocialvoidLib\Objects\Standard\Post::fromPost($post);

            // Start pre-resolving all entities before proceeding
            $SubPosts = [];
            $UserIDs = [];

            $UserIDs[$post->PosterUserID] = UserSearchMethod::ById;
            $MentionUserIDs = [];

            if($post->Repost !== null && $post->Repost->OriginalPostID)
                $SubPosts[$post->Repost->OriginalPostID] = PostSearchMethod::ByPublicId;
            if($post->Repost !== null && $post->Repost->OriginalUserID)
                $UserIDs[$post->Repost->OriginalUserID] = UserSearchMethod::ById;

            if($post->Quote !== null && $post->Quote->OriginalPostID)
                $SubPosts[$post->Quote->OriginalPostID] = PostSearchMethod::ByPublicId;
            if($post->Quote !== null && $post->Quote->OriginalUserID)
                $UserIDs[$post->Quote->OriginalUserID] = UserSearchMethod::ById;

            if($post->Reply !== null && $post->Reply->ReplyToPostID)
                $SubPosts[$post->Reply->ReplyToPostID] = PostSearchMethod::ByPublicId;
            if($post->Reply !== null && $post->Reply->ReplyToUserID)
                $UserIDs[(int)$post->Reply->ReplyToUserID] = UserSearchMethod::ById;

            if($post->OriginalPostThreadID !== null)
                $SubPosts[$post->OriginalPostThreadID] = PostSearchMethod::ByPublicId;

            foreach($post->TextEntities as $textEntity)
            {
                if($textEntity->Type == TextEntityType::Mention)
                    $MentionUserIDs[$textEntity->Value] = UserSearchMethod::ByUsername;
            }

            $ResolvedSubPosts = $this->networkSession->getSocialvoidLib()->getPostsManager()->getMultiplePosts($SubPosts, false);
            $ResolvedUsers = $this->networkSession->getSocialvoidLib()->getUserManager()->getMultipleUsers($UserIDs, false);
            $ResolvedMentionedUsers = $this->networkSession->getSocialvoidLib()->getUserManager()->getMultipleUsers($MentionUserIDs, true);

            // Sort results
            $SortedPostResolutions = [];
            $SortedUserResolutions = [];
            $MentionSubUserIDs = [];

            foreach($ResolvedSubPosts as $resolvedPost)
                $SortedPostResolutions[$resolvedPost->PublicID] = $resolvedPost;
            foreach($ResolvedUsers as $resolvedUser)
                $SortedUserResolutions[$resolvedUser->ID] = $resolvedUser;

            foreach($ResolvedSubPosts as $resolvedSubPost)
            {
                foreach($resolvedSubPost->TextEntities as $textEntity)
                {
                    if($textEntity->Type == TextEntityType::Mention)
                        $MentionSubUserIDs[$textEntity->Value] = UserSearchMethod::ByUsername;
                }
            }

            $ResolvedSubMentionedUsers = $this->networkSession->getSocialvoidLib()->getUserManager()->getMultipleUsers($MentionSubUserIDs, true);
            $SortedSubMentionedUsers = [];

            foreach($ResolvedSubMentionedUsers as $resolvedSubMentionedUser)
                $SortedSubMentionedUsers[$resolvedSubMentionedUser->Username] = Peer::fromUser($resolvedSubMentionedUser);


            $stdPost = \SocialvoidLib\Objects\Standard\Post::fromPost($post);
            $stdPost->Peer = Peer::fromUser($SortedUserResolutions[$post->PosterUserID]);

            foreach($post->Attachments as $attachment)
            {
                $stdPost->Attachments[] = Document::fromContentResults($this->networkSession->getCloud()->getDocument($attachment));
            }

            // Resolve reposted post
            if($post->Repost !== null && $post->Repost->OriginalPostID !== null && $first_layer)
            {
                $stdPost->RepostedPost = $this->getStandardPost($SortedPostResolutions[$post->Repost->OriginalPostID], false);
                $stdPost->RepostedPost->Flags = $this->getRelationalPostFlags($SortedPostResolutions[$post->Repost->OriginalPostID]);
            }

            // Resolve quoted post
            if($post->Quote !== null && $post->Quote->OriginalPostID && $first_layer)
            {
                $stdPost->QuotedPost = $this->getStandardPost($SortedPostResolutions[$post->Quote->OriginalPostID], false);
                $stdPost->QuotedPost->Flags = $this->getRelationalPostFlags($SortedPostResolutions[$post->Quote->OriginalPostID]);

                $mentionedUsernames = [];
                $stdPost->QuotedPost->MentionedPeers = [];
                foreach($SortedPostResolutions[$post->Quote->OriginalPostID]->TextEntities as $textEntity)
                {
                    if(isset($SortedSubMentionedUsers[$textEntity->Value]))
                    {
                        if(in_array($textEntity->Value, $mentionedUsernames))
                            continue;
                        $stdPost->QuotedPost->MentionedPeers[] = $SortedSubMentionedUsers[$textEntity->Value];
                        $mentionedUsernames[] = $textEntity->Value;
                    }
                }

                if($post->Quote->OriginalUserID !== null && Converter::hasFlag($SortedPostResolutions[$post->Quote->OriginalPostID]->Flags, PostFlags::Deleted) == false)
                {
                    $stdPost->QuotedPost->Peer = Peer::fromUser(
                        $SortedUserResolutions[$post->Quote->OriginalUserID]
                    );
                }

            }

            // Resolve replied post
            if($post->Reply !== null && $post->Reply->ReplyToPostID && $first_layer)
            {
                $stdPost->ReplyToPost = $this->getStandardPost($SortedPostResolutions[$post->Reply->ReplyToPostID], false);
                $stdPost->ReplyToPost->Flags = $this->getRelationalPostFlags($SortedPostResolutions[$post->Reply->ReplyToPostID]);

                if(Converter::hasFlag($stdPost->ReplyToPost->Flags, PostFlags::Deleted)  == false)
                {
                    $mentionedUsernames = [];
                    $stdPost->ReplyToPost->MentionedPeers = [];
                    foreach($SortedPostResolutions[$post->Reply->ReplyToPostID]->TextEntities as $textEntity)
                    {
                        if(isset($SortedSubMentionedUsers[$textEntity->Value]))
                        {
                            if(in_array($textEntity->Value, $mentionedUsernames))
                                continue;
                            $stdPost->ReplyToPost->MentionedPeers[] = $SortedSubMentionedUsers[$textEntity->Value];
                            $mentionedUsernames[] = $textEntity->Value;
                        }
                    }

                    $stdPost->ReplyToPost->Peer = Peer::fromUser(
                        $SortedUserResolutions[$post->Reply->ReplyToUserID]
                    );
                }
            }

            if($post->OriginalPostThreadID !== null && $first_layer)
            {
                $stdPost->OriginalThreadPost = $this->getStandardPost($SortedPostResolutions[$post->OriginalPostThreadID], false);
                $stdPost->OriginalThreadPost->Flags = $this->getRelationalPostFlags($SortedPostResolutions[$post->OriginalPostThreadID]);
                if(Converter::hasFlag($SortedPostResolutions[$post->OriginalPostThreadID]->Flags, PostFlags::Deleted)  == false)
                {
                    $mentionedUsernames = [];
                    $stdPost->OriginalThreadPost->MentionedPeers = [];
                    foreach($SortedPostResolutions[$post->OriginalPostThreadID]->TextEntities as $textEntity)
                    {
                        if(isset($SortedSubMentionedUsers[$textEntity->Value]))
                        {
                            if(in_array($textEntity->Value, $mentionedUsernames))
                                continue;
                            $stdPost->OriginalThreadPost->MentionedPeers[] = $SortedSubMentionedUsers[$textEntity->Value];
                            $mentionedUsernames[] = $textEntity->Value;
                        }
                    }
                }

            }

            if($first_layer)
            {
                foreach($ResolvedMentionedUsers as $user)
                {
                    $stdPost->MentionedPeers[] = Peer::fromUser($user);
                }

                // Register the standard post to cache
                // Disabled for now due to relational flags
                /**
                if(
                    $this->networkSession->getSocialvoidLib()->getRedisBasicCacheConfiguration()['Enabled'] &&
                    $this->networkSession->getSocialvoidLib()->getRedisBasicCacheConfiguration()['StandardPostCacheEnabled']
                )
                {
                    $CacheEntryInput = new RegisterCacheInput();
                    $CacheEntryInput->ObjectType = CacheEntryObjectType::StandardPost;
                    $CacheEntryInput->ObjectData = $stdPost->toArray();
                    $CacheEntryInput->Pointers = [$stdPost->ID];

                    try
                    {
                        $this->networkSession->getSocialvoidLib()->getBasicRedisCacheManager()->registerCache(
                            $CacheEntryInput,
                            $this->networkSession->getSocialvoidLib()->getRedisBasicCacheConfiguration()['StandardPostCacheTTL'],
                            $this->networkSession->getSocialvoidLib()->getRedisBasicCacheConfiguration()['StandardPostCacheLimit']
                        );
                    }
                    catch(Exception $e)
                    {
                        throw new CacheException('There was an error while trying to register the standard post cache entry', 0, $e);
                    }
                }
                 */
            }

            return $stdPost;
        }

        /**
         * Returns a timeline roster for basic information
         *
         * @return TimelineState
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws UserTimelineNotFoundException
         * @noinspection PhpUnused
         */
        public function getTimelineState(): TimelineState
        {
            return $this->networkSession->getSocialvoidLib()->getTimelineManager()->getTimelineState(
                TimelineSearchMethod::ByUserId, $this->networkSession->getAuthenticatedUser()->ID
            );
        }

        /**
         * Retrieves the timeline data and returns a standard post object that
         * automatically resolves the first and second layer of recursive data
         *
         * @param int $page_number
         * @param bool $recursive
         * @return \SocialvoidLib\Objects\Standard\Post[]
         * @throws AccessDeniedException
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws CannotGetOriginalImageException
         * @throws DatabaseException
         * @throws DependencyError
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws FileNotFoundException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws InvalidZimageFileException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws PostNotFoundException
         * @throws RedisCacheException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @throws SizeNotSetException
         * @throws TooManyAttachmentsException
         * @throws UnsupportedImageTypeException
         * @throws UserHasInvalidSlaveHashException
         * @throws UserTimelineNotFoundException
         * @noinspection DuplicatedCode
         */
        public function retrieveFeed(int $page_number, bool $recursive=True): array
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            if($page_number < 1) return [];

            $UserTimeline = $this->networkSession->getSocialvoidLib()->getTimelineManager()->retrieveTimeline(
                $this->networkSession->getAuthenticatedUser()
            );

            // Anti-Dumbass check
            if(count($UserTimeline->PostChunks) == 0) return [];
            if($page_number > count($UserTimeline->PostChunks)) return [];

            // Retrieve posts
            $ResolvedPostIds = [];
            $InvalidatedPostIDs = [];

            foreach($UserTimeline->PostChunks[($page_number - 1)] as $postID)
            {
                $StandardPost = $this->getStandardPost($postID);

                if($StandardPost->PostType == PostType::Deleted)
                {
                    $InvalidatedPostIDs[] = $postID;
                }
                else
                {
                    $ResolvedPostIds[] = $this->getStandardPost($postID);
                }
            }

            if(count($InvalidatedPostIDs) > 0)
            {
                // Update the timeline if there are invalidated posts to be removed
                $this->networkSession->getSocialvoidLib()->getTimelineManager()->removePosts(
                    $this->networkSession->getAuthenticatedUser(), $InvalidatedPostIDs
                );

                // Re-run the function since the timeline may have changed since this update.
                if($recursive) return $this->retrieveFeed($page_number, $recursive);
            }

            return $ResolvedPostIds;
        }

        /**
         * Reposts and existing post to the timeline
         *
         * @param string $post_public_id
         * @return Post
         * @throws AlreadyRepostedException
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DependencyError
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws RedisCacheException
         * @throws RepostRecordNotFoundException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @throws TooManyAttachmentsException
         * @throws UserHasInvalidSlaveHashException
         * @throws UserTimelineNotFoundException
         */
        public function repost(string $post_public_id): Post
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $selected_post = $this->networkSession->getSocialvoidLib()->getPostsManager()->getPost(
                PostSearchMethod::ByPublicId, $post_public_id);

            $PostObject = $this->networkSession->getSocialvoidLib()->getPostsManager()->repostPost(
                $this->networkSession->getAuthenticatedUser(),  $selected_post,
                $this->networkSession->getActiveSession()->ID, PostPriorityLevel::High
            );

            $this->networkSession->getSocialvoidLib()->getTimelineManager()->distributePost(
                $this->networkSession->getAuthenticatedUser(), $PostObject->PublicID, 15, true
            );

            return $PostObject;
        }

        /**
         * Likes a post, if not already liked
         *
         * @param string $post_public_id
         * @throws CacheException
         * @throws DatabaseException
         * @throws DependencyError
         * @throws DocumentNotFoundException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws LikeRecordNotFoundException
         * @throws NotAuthenticatedException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws RedisCacheException
         * @throws TooManyAttachmentsException
         */
        public function like(string $post_public_id): void
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $selected_post = $this->networkSession->getSocialvoidLib()->getPostsManager()->getPost(
                PostSearchMethod::ByPublicId, $post_public_id);

            $this->networkSession->getSocialvoidLib()->getPostsManager()->likePost(
                $this->networkSession->getAuthenticatedUser(), $selected_post
            );
        }

        /**
         * Likes a post, if not already liked
         *
         * @param string $post_public_id
         * @throws CacheException
         * @throws DatabaseException
         * @throws DependencyError
         * @throws DocumentNotFoundException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws LikeRecordNotFoundException
         * @throws NotAuthenticatedException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws RedisCacheException
         * @throws TooManyAttachmentsException
         */
        public function unlike(string $post_public_id): void
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $selected_post = $this->networkSession->getSocialvoidLib()->getPostsManager()->getPost(
                PostSearchMethod::ByPublicId, $post_public_id);

            $this->networkSession->getSocialvoidLib()->getPostsManager()->unlikePost(
                $this->networkSession->getAuthenticatedUser(), $selected_post
            );
        }

        /**
         * Returns the likes given to a certain post
         *
         * @param string $post_public_id
         * @param int $page
         * @return array
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws InvalidPageValueException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws PostNotFoundException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         */
        public function getLikes(string $post_public_id, int $page=1): array
        {
            /** @noinspection DuplicatedCode */
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            if($page < 0)
                throw new InvalidPageValueException('The cursor value cannot be a negative value');
            if($page < 1)
                throw new InvalidPageValueException('The cursor value must be a value greater than 0');

            $cursor_object = new Cursor(
                (int)$this->networkSession->getSocialvoidLib()->getMainConfiguration()['RetrieveLikesMaxLimit'], $page
            );

            $Likes = $this->networkSession->getSocialvoidLib()->getLikesRecordManager()->getLikes($post_public_id, $cursor_object->getOffset(), $cursor_object->ContentLimit);

            $search_query = [];
            foreach($Likes as $user_id)
            {
                $search_query[$user_id] = UserSearchMethod::ById;
            }

            return $this->networkSession->getUsers()->resolveMultiplePeers($search_query);
        }

        /**
         * Returns an array of posts that replied to the selected post
         *
         * @param string $post_public_id
         * @param int $page
         * @return \SocialvoidLib\Objects\Standard\Post[]
         * @throws AccessDeniedException
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws CannotGetOriginalImageException
         * @throws DatabaseException
         * @throws DependencyError
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws FileNotFoundException
         * @throws InvalidPageValueException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws InvalidZimageFileException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws PostNotFoundException
         * @throws RedisCacheException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @throws SizeNotSetException
         * @throws TooManyAttachmentsException
         * @throws UnsupportedImageTypeException
         * @noinspection DuplicatedCode
         */
        public function getReplies(string $post_public_id, int $page=1): array
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            if($page < 0)
                throw new InvalidPageValueException('The cursor value cannot be a negative value');
            if($page < 1)
                throw new InvalidPageValueException('The cursor value must be a value greater than 0');

            $cursor_object = new Cursor(
                (int)$this->networkSession->getSocialvoidLib()->getMainConfiguration()['RetrieveRepliesMaxLimit'], $page
            );

            $Replies = $this->networkSession->getSocialvoidLib()->getReplyRecordManager()->getReplies($post_public_id, $cursor_object->getOffset(), $cursor_object->ContentLimit);
            $StdPosts = [];
            foreach($Replies as $reply_id)
                $StdPosts[] = $this->getStandardPost($reply_id);
            return $StdPosts;
        }

        /**
         * Retrieves an array of posts that quoted the selected post
         *
         * @param string $post_public_id
         * @param int $page
         * @return \SocialvoidLib\Objects\Standard\Post[]
         * @throws AccessDeniedException
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws CannotGetOriginalImageException
         * @throws DatabaseException
         * @throws DependencyError
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws FileNotFoundException
         * @throws InvalidPageValueException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws InvalidZimageFileException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws PostNotFoundException
         * @throws RedisCacheException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @throws SizeNotSetException
         * @throws TooManyAttachmentsException
         * @throws UnsupportedImageTypeException
         * @noinspection DuplicatedCode
         */
        public function getQuotes(string $post_public_id, int $page=1): array
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            if($page < 0)
                throw new InvalidPageValueException('The cursor value cannot be a negative value');
            if($page < 1)
                throw new InvalidPageValueException('The cursor value must be a value greater than 0');

            $cursor_object = new Cursor(
                (int)$this->networkSession->getSocialvoidLib()->getMainConfiguration()['RetrieveQuotesMaxLimit'], $page
            );

            $Replies = $this->networkSession->getSocialvoidLib()->getQuotesRecordManager()->getQuotes($post_public_id, $cursor_object->getOffset(), $cursor_object->ContentLimit);
            $StdPosts = [];
            foreach($Replies as $reply_id)
                $StdPosts[] = $this->getStandardPost($reply_id);
            return $StdPosts;
        }

        /**
         * Returns an array of users that reposted the selected post
         *
         * @param string $post_public_id
         * @param int $page
         * @return User[]
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws InvalidPageValueException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws PostNotFoundException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @noinspection DuplicatedCode
         */
        public function getReposts(string $post_public_id, int $page=1): array
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            if($page < 0)
                throw new InvalidPageValueException('The cursor value cannot be a negative value');
            if($page < 1)
                throw new InvalidPageValueException('The cursor value must be a value greater than 0');

            $cursor_object = new Cursor(
                (int)$this->networkSession->getSocialvoidLib()->getMainConfiguration()['RetrieveRepostsMaxLimit'], $page
            );

            $UserIds = $this->networkSession->getSocialvoidLib()->getRepostsRecordManager()->getReposts($post_public_id, $cursor_object->getOffset(), $cursor_object->ContentLimit);
            $search_query = [];
            foreach($UserIds as $user_id)
                $search_query[$user_id] = UserSearchMethod::ById;

            return $this->networkSession->getUsers()->resolveMultiplePeers($search_query);
        }

        /**
         * Distributes a new post to the timeline, and it's users
         *
         * @param string $post_public_id
         * @param string $text
         * @param array $attachments
         * @param array $flags
         * @return Post
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DependencyError
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws InvalidPostTextException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws QuoteRecordNotFoundException
         * @throws RedisCacheException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @throws TooManyAttachmentsException
         * @throws UserHasInvalidSlaveHashException
         * @throws UserTimelineNotFoundException
         */
        public function quote(string $post_public_id, string $text, array $attachments=[], array $flags=[]): Post
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $selected_post = $this->networkSession->getSocialvoidLib()->getPostsManager()->getPost(
                PostSearchMethod::ByPublicId, $post_public_id);

            $PostObject = $this->networkSession->getSocialvoidLib()->getPostsManager()->quotePost(
                $this->networkSession->getAuthenticatedUser(), $selected_post, $text,
                Converter::getSource($this->networkSession->getActiveSession()),
                $this->networkSession->getActiveSession()->ID, $attachments,
                PostPriorityLevel::High, $flags
            );

            $this->networkSession->getSocialvoidLib()->getTimelineManager()->distributePost(
                $this->networkSession->getAuthenticatedUser(), $PostObject->PublicID, 15, true
            );

            return $PostObject;
        }

        /**
         * Replies to an existing post
         *
         * @param string $post_public_id
         * @param string $text
         * @param array $media_content
         * @param array $flags
         * @return Post
         * @throws CacheException
         * @throws DatabaseException
         * @throws DependencyError
         * @throws DocumentNotFoundException
         * @throws InvalidPostTextException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws NotAuthenticatedException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws RedisCacheException
         * @throws ReplyRecordNotFoundException
         * @throws TooManyAttachmentsException
         */
        public function reply(string $post_public_id, string $text, array $media_content=[], array $flags=[]): Post
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $selected_post = $this->networkSession->getSocialvoidLib()->getPostsManager()->getPost(
                PostSearchMethod::ByPublicId, $post_public_id);

            return $this->networkSession->getSocialvoidLib()->getPostsManager()->replyToPost(
                $this->networkSession->getAuthenticatedUser(), $selected_post, $text,
                Converter::getSource($this->networkSession->getActiveSession()),
                $this->networkSession->getActiveSession()->ID, $media_content,
                PostPriorityLevel::High, $flags
            );
        }

        /**
         * Deletes an existing post
         *
         * @param string $post_public_id
         * @return bool
         * @throws AccessDeniedException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DependencyError
         * @throws DocumentNotFoundException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws NotAuthenticatedException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws QuoteRecordNotFoundException
         * @throws RedisCacheException
         * @throws ReplyRecordNotFoundException
         * @throws RepostRecordNotFoundException
         * @throws TooManyAttachmentsException
         */
        public function delete(string $post_public_id): bool
        {
            if ($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $selected_post = $this->networkSession->getSocialvoidLib()->getPostsManager()->getPost(
                PostSearchMethod::ByPublicId, $post_public_id);

            if($selected_post->PosterUserID !== $this->networkSession->getAuthenticatedUser()->ID)
                throw new AccessDeniedException('Insufficient permissions to delete this post');

            $this->networkSession->getSocialvoidLib()->getPostsManager()->deletePost($selected_post);

            return true;
        }
    }
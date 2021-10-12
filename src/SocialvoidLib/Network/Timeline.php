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
    use SocialvoidLib\Exceptions\GenericInternal\DisplayPictureException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSlaveHashException;
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
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidCursorValueException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPostTextException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Cursor;
    use SocialvoidLib\Objects\Post;
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
         * @param array $media_content
         * @param array $flags
         * @return Post
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws InvalidPostTextException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws PostNotFoundException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @throws UserHasInvalidSlaveHashException
         * @throws UserTimelineNotFoundException
         */
        public function compose(string $text, array $media_content=[], array $flags=[]): Post
        {
            if ($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $PostObject = $this->networkSession->getSocialvoidLib()->getPostsManager()->publishPost(
                $this->networkSession->getAuthenticatedUser(),
                Converter::getSource($this->networkSession->getActiveSession()),
                $text, $this->networkSession->getActiveSession()->ID, $media_content,
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
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws NotAuthenticatedException
         * @throws PostNotFoundException
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
         * Retrieves a standard post object while resolving all of it's contents
         *
         * @param string $post_id
         * @return \SocialvoidLib\Objects\Standard\Post
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws CannotGetOriginalImageException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws FileNotFoundException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws InvalidZimageFileException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws PostNotFoundException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @throws SizeNotSetException
         * @throws UnsupportedImageTypeException
         * @throws DisplayPictureException
         * @noinspection DuplicatedCode
         */
        public function getStandardPost(string $post_id): \SocialvoidLib\Objects\Standard\Post
        {
            if ($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $post = $this->networkSession->getSocialvoidLib()->getPostsManager()->getPost(
                PostSearchMethod::ByPublicId, $post_id
            );

            if(Converter::hasFlag($post->Flags, PostFlags::Deleted))
                return \SocialvoidLib\Objects\Standard\Post::fromPost($post);

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

            // Resolve reposted post
            if($post->Repost !== null && $post->Repost->OriginalPostID !== null)
            {
                if(Converter::hasFlag($SortedPostResolutions[$post->Repost->OriginalPostID]->Flags, PostFlags::Deleted) == false)
                {
                    $stdPost->RepostedPost = $this->getStandardPost($post->Repost->OriginalPostID);

                    try
                    {
                        if ($this->networkSession->getSocialvoidLib()->getLikesRecordManager()->getRecord(
                            $this->networkSession->getAuthenticatedUser()->ID, $post->Repost->OriginalPostID)->Liked
                        ) {
                            $stdPost->RepostedPost->Flags[] = PostFlags::Liked;
                        }
                    }
                    catch (LikeRecordNotFoundException $e)
                    {
                        unset($e);
                    }

                    try
                    {
                        if($this->networkSession->getSocialvoidLib()->getRepostsRecordManager()->getRecord(
                            $this->networkSession->getAuthenticatedUser()->ID, $post->Repost->OriginalPostID)->Reposted
                        )
                        {
                            $stdPost->RepostedPost->Flags[] = PostFlags::Reposted;
                        }
                    }
                    catch(RepostRecordNotFoundException $e)
                    {
                        unset($e);
                    }
                }
            }

            // Resolve quoted post
            if($post->Quote !== null && $post->Quote->OriginalPostID)
            {
                $stdPost->QuotedPost = \SocialvoidLib\Objects\Standard\Post::fromPost(
                    $SortedPostResolutions[$post->Quote->OriginalPostID]
                );

                try
                {
                    if($this->networkSession->getSocialvoidLib()->getLikesRecordManager()->getRecord(
                        $this->networkSession->getAuthenticatedUser()->ID, $post->Quote->OriginalPostID)->Liked
                    )
                    {
                        $stdPost->QuotedPost->Flags[] = PostFlags::Liked;
                    }
                }
                catch (LikeRecordNotFoundException $e)
                {
                    unset($e);
                }

                try
                {
                    if($this->networkSession->getSocialvoidLib()->getRepostsRecordManager()->getRecord(
                        $this->networkSession->getAuthenticatedUser()->ID, $post->Quote->OriginalPostID)->Reposted
                    )
                    {
                        $stdPost->QuotedPost->Flags[] = PostFlags::Reposted;
                    }
                }
                catch(RepostRecordNotFoundException $e)
                {
                    unset($e);
                }

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
            if($post->Reply !== null && $post->Reply->ReplyToPostID)
            {
                $stdPost->ReplyToPost = \SocialvoidLib\Objects\Standard\Post::fromPost(
                    $SortedPostResolutions[$post->Reply->ReplyToPostID]
                );

                try
                {
                    if($this->networkSession->getSocialvoidLib()->getLikesRecordManager()->getRecord(
                        $this->networkSession->getAuthenticatedUser()->ID, $post->Reply->ReplyToPostID)->Liked
                    )
                    {
                        $stdPost->ReplyToPost->Flags[] = PostFlags::Liked;
                    }
                }
                catch (LikeRecordNotFoundException $e)
                {
                    unset($e);
                }

                try
                {
                    if($this->networkSession->getSocialvoidLib()->getRepostsRecordManager()->getRecord(
                        $this->networkSession->getAuthenticatedUser()->ID, $post->Reply->ReplyToPostID)->Reposted
                    )
                    {
                        $stdPost->ReplyToPost->Flags[] = PostFlags::Reposted;
                    }
                }
                catch(RepostRecordNotFoundException $e)
                {
                    unset($e);
                }

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

                if($post->Reply->ReplyToUserID !== null && Converter::hasFlag($SortedPostResolutions[$post->Reply->ReplyToPostID]->Flags, PostFlags::Deleted) == false)
                {
                    $stdPost->ReplyToPost->Peer = Peer::fromUser(
                        $SortedUserResolutions[$post->Reply->ReplyToUserID]
                    );
                }
            }

            foreach($ResolvedMentionedUsers as $user)
            {
                $stdPost->MentionedPeers[] = Peer::fromUser($user);
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
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws CannotGetOriginalImageException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws FileNotFoundException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws InvalidZimageFileException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws PostNotFoundException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @throws SizeNotSetException
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
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws RepostRecordNotFoundException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
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
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws LikeRecordNotFoundException
         * @throws NotAuthenticatedException
         * @throws PostDeletedException
         * @throws PostNotFoundException
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
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws LikeRecordNotFoundException
         * @throws NotAuthenticatedException
         * @throws PostDeletedException
         * @throws PostNotFoundException
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
         * @param int $cursor
         * @return array
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws InvalidCursorValueException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws PostNotFoundException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         */
        public function getLikes(string $post_public_id, int $cursor=1): array
        {
            /** @noinspection DuplicatedCode */
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            if($cursor < 0)
                throw new InvalidCursorValueException('The cursor value cannot be a negative value');
            if($cursor < 1)
                throw new InvalidCursorValueException('The cursor value must be a value greater than 0');

            $cursor_object = new Cursor(
                (int)$this->networkSession->getSocialvoidLib()->getMainConfiguration()['RetrieveLikesMaxLimit'], $cursor
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
         * @param int $cursor
         * @return \SocialvoidLib\Objects\Standard\Post[]
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws CannotGetOriginalImageException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws FileNotFoundException
         * @throws InvalidCursorValueException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws InvalidZimageFileException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws PostNotFoundException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @throws SizeNotSetException
         * @throws UnsupportedImageTypeException
         * @noinspection DuplicatedCode
         */
        public function getReplies(string $post_public_id, int $cursor=1): array
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            if($cursor < 0)
                throw new InvalidCursorValueException('The cursor value cannot be a negative value');
            if($cursor < 1)
                throw new InvalidCursorValueException('The cursor value must be a value greater than 0');

            $cursor_object = new Cursor(
                (int)$this->networkSession->getSocialvoidLib()->getMainConfiguration()['RetrieveRepliesMaxLimit'], $cursor
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
         * @param int $cursor
         * @return \SocialvoidLib\Objects\Standard\Post[]
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws CannotGetOriginalImageException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws FileNotFoundException
         * @throws InvalidCursorValueException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws InvalidZimageFileException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws PostNotFoundException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @throws SizeNotSetException
         * @throws UnsupportedImageTypeException
         * @noinspection DuplicatedCode
         */
        public function getQuotes(string $post_public_id, int $cursor=1): array
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            if($cursor < 0)
                throw new InvalidCursorValueException('The cursor value cannot be a negative value');
            if($cursor < 1)
                throw new InvalidCursorValueException('The cursor value must be a value greater than 0');

            $cursor_object = new Cursor(
                (int)$this->networkSession->getSocialvoidLib()->getMainConfiguration()['RetrieveQuotesMaxLimit'], $cursor
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
         * @param int $cursor
         * @return User[]
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws InvalidCursorValueException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws PostNotFoundException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @noinspection DuplicatedCode
         */
        public function getReposts(string $post_public_id, int $cursor=1): array
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            if($cursor < 0)
                throw new InvalidCursorValueException('The cursor value cannot be a negative value');
            if($cursor < 1)
                throw new InvalidCursorValueException('The cursor value must be a value greater than 0');

            $cursor_object = new Cursor(
                (int)$this->networkSession->getSocialvoidLib()->getMainConfiguration()['RetrieveRepostsMaxLimit'], $cursor
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
         * @param array $media_content
         * @param array $flags
         * @return Post
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws DatabaseException
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
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @throws UserHasInvalidSlaveHashException
         * @throws UserTimelineNotFoundException
         */
        public function quote(string $post_public_id, string $text, array $media_content=[], array $flags=[]): Post
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $selected_post = $this->networkSession->getSocialvoidLib()->getPostsManager()->getPost(
                PostSearchMethod::ByPublicId, $post_public_id);

            $PostObject = $this->networkSession->getSocialvoidLib()->getPostsManager()->quotePost(
                $this->networkSession->getAuthenticatedUser(), $selected_post, $text,
                Converter::getSource($this->networkSession->getActiveSession()),
                $this->networkSession->getActiveSession()->ID, $media_content,
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
         * @throws InvalidPostTextException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws NotAuthenticatedException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws ReplyRecordNotFoundException
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
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws NotAuthenticatedException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws QuoteRecordNotFoundException
         * @throws ReplyRecordNotFoundException
         * @throws RepostRecordNotFoundException
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
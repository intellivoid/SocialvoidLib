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

    use SocialvoidLib\Abstracts\Flags\PostFlags;
    use SocialvoidLib\Abstracts\Levels\PostPriorityLevel;
    use SocialvoidLib\Abstracts\SearchMethods\PostSearchMethod;
    use SocialvoidLib\Abstracts\SearchMethods\TimelineSearchMethod;
    use SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod;
    use SocialvoidLib\Classes\Converter;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Exceptions\GenericInternal\BackgroundWorkerNotEnabledException;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSlaveHashException;
    use SocialvoidLib\Exceptions\GenericInternal\ServiceJobException;
    use SocialvoidLib\Exceptions\Internal\FollowerDataNotFound;
    use SocialvoidLib\Exceptions\Internal\UserTimelineNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Network\AlreadyRepostedException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PostDeletedException;
    use SocialvoidLib\Exceptions\Standard\Network\PostNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPeerInputException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPostTextException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Post;
    use SocialvoidLib\Objects\Standard\Peer;
    use SocialvoidLib\Objects\Standard\TimelineState;
    use udp2\Exceptions\AvatarGeneratorException;
    use udp2\Exceptions\AvatarNotFoundException;
    use udp2\Exceptions\ImageTooSmallException;
    use udp2\Exceptions\UnsupportedAvatarGeneratorException;
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
         * @throws FollowerDataNotFound
         * @throws InvalidPostTextException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws NotAuthenticatedException
         * @throws PostNotFoundException
         * @throws UserTimelineNotFoundException
         */
        public function composePost(string $text, array $media_content=[], array $flags=[]): Post
        {
            if ($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $PostObject = $this->networkSession->getSocialvoidLib()->getPostsManager()->publishPost(
                $this->networkSession->getAuthenticatedUser(),
                Converter::getSource($this->networkSession->getActiveSession()),
                $text, $this->networkSession->getActiveSession()->ID, $media_content,
                PostPriorityLevel::High, $flags
            );

            $FollowerData = $this->networkSession->getSocialvoidLib()->getFollowerDataManager()->resolveRecord(
                $this->networkSession->getAuthenticatedUser()->ID
            );

            $FollowerData->FollowersIDs[] = $this->networkSession->getAuthenticatedUser()->ID;
            $this->networkSession->getSocialvoidLib()->getTimelineManager()->distributePost(
                $PostObject->PublicID, $FollowerData->FollowersIDs, 100, true
            );

            return $PostObject;
        }

        /**
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
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws PeerNotFoundException
         * @throws PostNotFoundException
         * @throws ServiceJobException
         * @throws UserTimelineNotFoundException
         * @throws DocumentNotFoundException
         * @throws CannotGetOriginalImageException
         * @throws FileNotFoundException
         * @throws InvalidZimageFileException
         * @throws SizeNotSetException
         * @throws UnsupportedImageTypeException
         * @throws AvatarGeneratorException
         * @throws AvatarNotFoundException
         * @throws ImageTooSmallException
         * @throws UnsupportedAvatarGeneratorException
         */
        public function retrieveTimeline(int $page_number, bool $recursive=True): array
        {
            if($page_number < 1) return [];

            $UserTimeline = $this->networkSession->getSocialvoidLib()->getTimelineManager()->retrieveTimeline(
                $this->networkSession->getAuthenticatedUser()->ID
            );

            // Anti-Dumbass check
            if(count($UserTimeline->PostChunks) == 0) return [];
            if($page_number > count($UserTimeline->PostChunks)) return [];

            // Retrieve posts
            $PostsIDs = array_fill_keys($UserTimeline->PostChunks[($page_number - 1)], PostSearchMethod::ByPublicId);
            $ResolvedPosts = $this->networkSession->getSocialvoidLib()->getPostsManager()->getMultiplePosts(
                $PostsIDs, true);

            $SubPosts = [];
            $UserIDs = [];
            foreach($ResolvedPosts as $post)
            {
                $UserIDs[$post->PosterUserID] = UserSearchMethod::ById;

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
            }


            $ResolvedSubPosts = $this->networkSession->getSocialvoidLib()->getPostsManager()->getMultiplePosts(
                $SubPosts, false);
            $ResolvedUsers = $this->networkSession->getSocialvoidLib()->getUserManager()->getMultipleUsers(
                $UserIDs, false);

            // Sort results
            $SortedPostResolutions = [];
            $SortedUserResolutions = [];
            $InvalidatedPostIDs = [];

            foreach($ResolvedPosts as $resolvedPost)
                $SortedPostResolutions[$resolvedPost->PublicID] = $resolvedPost;
            foreach($ResolvedSubPosts as $resolvedPost)
                $SortedPostResolutions[$resolvedPost->PublicID] = $resolvedPost;
            foreach($ResolvedUsers as $resolvedUser)
                $SortedUserResolutions[$resolvedUser->ID] = $resolvedUser;

            $ReturnResults = [];
            foreach($ResolvedPosts as $resolvedPost)
            {
                if(Converter::hasFlag($resolvedPost->Flags, PostFlags::Deleted))
                {
                    $InvalidatedPostIDs[] = $resolvedPost->PublicID;
                    continue;
                }

                $stdPost = \SocialvoidLib\Objects\Standard\Post::fromPost($resolvedPost);
                $stdPost->Peer = Peer::fromUser($SortedUserResolutions[$resolvedPost->PosterUserID]);

                // Resolve quoted post
                if($resolvedPost->Quote !== null && $resolvedPost->Quote->OriginalPostID)
                {
                    $stdPost->QuotedPost = \SocialvoidLib\Objects\Standard\Post::fromPost(
                        $SortedPostResolutions[$resolvedPost->Quote->OriginalPostID]
                    );

                    if($resolvedPost->Quote->OriginalUserID !== null)
                    {
                        $stdPost->QuotedPost->Peer = Peer::fromUser(
                            $SortedUserResolutions[$resolvedPost->Quote->OriginalUserID]
                        );
                    }

                }

                if($resolvedPost->Repost !== null && $resolvedPost->Repost->OriginalPostID !== null)
                {
                    if(Converter::hasFlag($SortedPostResolutions[$resolvedPost->Repost->OriginalPostID]->Flags, PostFlags::Deleted))
                    {
                        // This is an invalidated post since the original repost has been deleted
                        $InvalidatedPostIDs[] = $resolvedPost->PublicID;
                        $InvalidatedPostIDs[] = $resolvedPost->Repost->OriginalPostID; // To be on the safe side.
                    }
                    else
                    {

                        $stdPost->RepostedPost = \SocialvoidLib\Objects\Standard\Post::fromPost(
                            $SortedPostResolutions[$resolvedPost->Repost->OriginalPostID]
                        );

                        if($resolvedPost->Repost->OriginalUserID !== null)
                        {
                            $stdPost->RepostedPost->Peer = Peer::fromUser(
                                $SortedUserResolutions[$resolvedPost->Repost->OriginalUserID]
                            );
                        }
                    }
                }

                if($resolvedPost->Reply !== null && $resolvedPost->Reply->ReplyToPostID)
                {
                    $stdPost->ReplyToPost = \SocialvoidLib\Objects\Standard\Post::fromPost(
                        $SortedPostResolutions[$resolvedPost->Reply->ReplyToPostID]
                    );

                    if($resolvedPost->Reply->ReplyToUserID !== null)
                    {
                        $stdPost->ReplyToPost->Peer = Peer::fromUser(
                            $SortedUserResolutions[$resolvedPost->Reply->ReplyToUserID]
                        );
                    }
                }

                $ReturnResults[] = $stdPost;
            }

            if(count($InvalidatedPostIDs) > 0)
            {
                // Update the timeline if there are invalidated posts to be removed
                $this->networkSession->getSocialvoidLib()->getTimelineManager()->removePosts(
                    $this->networkSession->getAuthenticatedUser()->ID, $InvalidatedPostIDs
                );

                // Re-run the function since the timeline may have changed since this update.
                if($recursive) return $this->retrieveTimeline($page_number, $recursive);
            }

            return $ReturnResults;
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
         * @throws FollowerDataNotFound
         * @throws InvalidSearchMethodException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws UserTimelineNotFoundException
         * @throws InvalidSlaveHashException
         */
        public function repostToTimeline(string $post_public_id): Post
        {
            $selected_post = $this->networkSession->getSocialvoidLib()->getPostsManager()->getPost(
                PostSearchMethod::ByPublicId, $post_public_id);

            $PostObject = $this->networkSession->getSocialvoidLib()->getPostsManager()->repostPost(
                $this->networkSession->getAuthenticatedUser()->ID,  $selected_post,
                $this->networkSession->getActiveSession()->ID, PostPriorityLevel::High
            );

            $FollowerData = $this->networkSession->getSocialvoidLib()->getFollowerDataManager()->resolveRecord(
                $this->networkSession->getAuthenticatedUser()->ID
            );

            // TODO: The distribution method should check if the repost already exists in the timeline
            $FollowerData->FollowersIDs[] = $this->networkSession->getAuthenticatedUser()->ID;
            $this->networkSession->getSocialvoidLib()->getTimelineManager()->distributePost(
                $PostObject->PublicID, $FollowerData->FollowersIDs, 100, true
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
         * @throws PostDeletedException
         * @throws PostNotFoundException
         */
        public function likePost(string $post_public_id): void
        {
            $selected_post = $this->networkSession->getSocialvoidLib()->getPostsManager()->getPost(
                PostSearchMethod::ByPublicId, $post_public_id);

            $this->networkSession->getSocialvoidLib()->getPostsManager()->likePost(
                $this->networkSession->getAuthenticatedUser(), $selected_post
            );
        }

        /**
         * Returns the likes given to a certain post
         *
         * @param string $post_public_id
         * @param int $offset
         * @param int $limit
         * @return array
         * @throws AvatarGeneratorException
         * @throws AvatarNotFoundException
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws CannotGetOriginalImageException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws FileNotFoundException
         * @throws ImageTooSmallException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws InvalidZimageFileException
         * @throws PeerNotFoundException
         * @throws ServiceJobException
         * @throws SizeNotSetException
         * @throws UnsupportedAvatarGeneratorException
         * @throws UnsupportedImageTypeException
         */
        public function getLikes(string $post_public_id, int $offset=0, int $limit=100): array
        {
            $Likes = $this->networkSession->getSocialvoidLib()->getLikesRecordManager()->getLikes(
                Utilities::getSlaveHash($post_public_id), Utilities::removeSlaveHash($post_public_id),
                $offset, $limit
            );

            $search_query = [];
            foreach($Likes as $user_id)
            {
                $search_query[$user_id] = UserSearchMethod::ById;
            }

            return $this->networkSession->getUsers()->resolveMultiplePeers($search_query);
        }

        /**
         * Likes a post, if not already liked
         *
         * @param string $post_public_id
         * @throws CacheException
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws InvalidSlaveHashException
         */
        public function unlikePost(string $post_public_id): void
        {
            $selected_post = $this->networkSession->getSocialvoidLib()->getPostsManager()->getPost(
                PostSearchMethod::ByPublicId, $post_public_id);

            $this->networkSession->getSocialvoidLib()->getPostsManager()->unlikePost(
                $this->networkSession->getAuthenticatedUser(), $selected_post
            );
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
         * @throws FollowerDataNotFound
         * @throws InvalidPostTextException
         * @throws InvalidSearchMethodException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws UserTimelineNotFoundException
         * @throws InvalidSlaveHashException
         */
        public function quotePost(string $post_public_id, string $text, array $media_content=[], array $flags=[]): Post
        {
            $selected_post = $this->networkSession->getSocialvoidLib()->getPostsManager()->getPost(
                PostSearchMethod::ByPublicId, $post_public_id);

            $PostObject = $this->networkSession->getSocialvoidLib()->getPostsManager()->quotePost(
                $this->networkSession->getAuthenticatedUser()->ID, $selected_post, $text,
                Converter::getSource($this->networkSession->getActiveSession()),
                $this->networkSession->getActiveSession()->ID, $media_content,
                PostPriorityLevel::High, $flags
            );

            $FollowerData = $this->networkSession->getSocialvoidLib()->getFollowerDataManager()->resolveRecord(
                $this->networkSession->getAuthenticatedUser()->ID
            );

            $FollowerData->FollowersIDs[] = $this->networkSession->getAuthenticatedUser()->ID;
            $this->networkSession->getSocialvoidLib()->getTimelineManager()->distributePost(
                $PostObject->PublicID, $FollowerData->FollowersIDs, 100, true
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
         * @throws FollowerDataNotFound
         * @throws InvalidPostTextException
         * @throws InvalidSearchMethodException
         * @throws PostDeletedException
         * @throws PostNotFoundException
         * @throws InvalidSlaveHashException
         */
        public function replyToPost(string $post_public_id, string $text, array $media_content=[], array $flags=[]): Post
        {
            $selected_post = $this->networkSession->getSocialvoidLib()->getPostsManager()->getPost(
                PostSearchMethod::ByPublicId, $post_public_id);

            $PostObject = $this->networkSession->getSocialvoidLib()->getPostsManager()->replyToPost(
                $this->networkSession->getAuthenticatedUser()->ID, $selected_post, $text,
                Converter::getSource($this->networkSession->getActiveSession()),
                $this->networkSession->getActiveSession()->ID, $media_content,
                PostPriorityLevel::High, $flags
            );

            $FollowerData = $this->networkSession->getSocialvoidLib()->getFollowerDataManager()->resolveRecord(
                $this->networkSession->getAuthenticatedUser()->ID
            );

            $FollowerData->FollowersIDs[] = $this->networkSession->getAuthenticatedUser()->ID;

            return $PostObject;
        }
    }
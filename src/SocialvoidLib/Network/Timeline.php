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


    use SocialvoidLib\Abstracts\Levels\PostPriorityLevel;
    use SocialvoidLib\Abstracts\SearchMethods\PostSearchMethod;
    use SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod;
    use SocialvoidLib\Classes\Converter;
    use SocialvoidLib\Exceptions\GenericInternal\BackgroundWorkerNotEnabledException;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\GenericInternal\ServiceJobException;
    use SocialvoidLib\Exceptions\Internal\FollowerDataNotFound;
    use SocialvoidLib\Exceptions\Internal\UserTimelineNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PostNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPostTextException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Post;
    use SocialvoidLib\Objects\Standard\TimelineRoster;

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
         * Distributes a new post to the timeline and it's users
         *
         * @param string $text
         * @param array $media_content
         * @param array $flags
         * @return Post
         * @throws DatabaseException
         * @throws FollowerDataNotFound
         * @throws InvalidPostTextException
         * @throws InvalidSearchMethodException
         * @throws PostNotFoundException
         * @throws UserTimelineNotFoundException
         * @throws BackgroundWorkerNotEnabledException
         */
        public function postToTimeline(string $text, array $media_content=[], $flags=[]): Post
        {
            $PostObject = $this->networkSession->getSocialvoidLib()->getPostsManager()->publishPost(
                $this->networkSession->getAuthenticatedUser()->ID,
                Converter::getSource($this->networkSession->getActiveSession()),
                $text, $this->networkSession->getActiveSession()->ID, $media_content,
                PostPriorityLevel::High, $flags
            );

            $FollowerData = $this->networkSession->getSocialvoidLib()->getFollowerDataManager()->resolveRecord(
                $this->networkSession->getAuthenticatedUser()->ID
            );

            $FollowerData->FollowersIDs[] = $this->networkSession->getAuthenticatedUser()->ID;
            $this->networkSession->getSocialvoidLib()->getTimelineManager()->distributePost(
                $PostObject->ID, $FollowerData->FollowersIDs, 100, true
            );

            return $PostObject;
        }

        /**
         * Returns a timeline roster for basic information
         *
         * @return TimelineRoster
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws UserTimelineNotFoundException
         */
        public function getTimelineRoster(): TimelineRoster
        {
            // TODO: Optimize the query for this
            $UserTimeline = $this->networkSession->getSocialvoidLib()->getTimelineManager()->retrieveTimeline(
                $this->networkSession->getAuthenticatedUser()->ID
            );

            $TimelineRoster = new TimelineRoster();
            $TimelineRoster->TimelineLastUpdated = $UserTimeline->LastUpdatedTimestamp;
            $TimelineRoster->TimelinePostsCount = $UserTimeline->NewPosts;

            return $TimelineRoster;
        }

        /**
         * Retrieves the timeline data
         *
         * @param int $page_number
         * @return array
         * @throws BackgroundWorkerNotEnabledException
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws PostNotFoundException
         * @throws UserTimelineNotFoundException
         * @throws CacheException
         * @throws ServiceJobException
         * @throws PeerNotFoundException
         */
        public function retrieveTimeline(int $page_number): array
        {
            if($page_number < 1) return [];

            $UserTimeline = $this->networkSession->getSocialvoidLib()->getTimelineManager()->retrieveTimeline(
                $this->networkSession->getAuthenticatedUser()->ID
            );

            // Anti-Dumbass check
            if(count($UserTimeline->PostChunks) == 0) return [];
            if($page_number > count($UserTimeline->PostChunks)) return [];

            // Retrieve posts
            $PostsIDs = array_fill_keys($UserTimeline->PostChunks[($page_number - 1)], PostSearchMethod::ById);
            $ResolvedPosts = $this->networkSession->getSocialvoidLib()->getPostsManager()->getMultiplePosts(
                $PostsIDs, true);

            $SubPosts = [];
            $UserIDs = [];
            foreach($ResolvedPosts as $post)
            {
                $UserIDs[$post->PosterUserID] = PostSearchMethod::ById;

                if($post->Repost !== null && $post->Repost->OriginalPostID)
                    $SubPosts[$post->Repost->OriginalPostID] = PostSearchMethod::ById;
                if($post->Repost !== null && $post->Repost->OriginalUserID)
                    $UserIDs[$post->Repost->OriginalUserID] = UserSearchMethod::ById;

                if($post->Quote !== null && $post->Quote->OriginalPostID)
                    $SubPosts[$post->Quote->OriginalPostID] = PostSearchMethod::ById;
                if($post->Quote !== null && $post->Quote->OriginalUserID)
                    $UserIDs[$post->Quote->OriginalUserID] = UserSearchMethod::ById;

                if($post->Reply !== null && $post->Reply->ReplyToPostID)
                    $SubPosts[$post->Reply->ReplyToPostID] = PostSearchMethod::ById;
                if($post->Reply !== null && $post->Reply->ReplyToUserID)
                    $UserIDs[$post->Reply->ReplyToUserID] = UserSearchMethod::ById;
            }

            $ResolvedSubPosts = $this->networkSession->getSocialvoidLib()->getPostsManager()->getMultiplePosts(
                $SubPosts, true);
            $ResolvedUsers = $this->networkSession->getSocialvoidLib()->getUserManager()->getMultipleUsers(
                $UserIDs, true
            );

            return [
                "posts" => $ResolvedPosts,
                "sub_posts" => $ResolvedSubPosts,
                "users" => $ResolvedUsers
            ];
        }
    }
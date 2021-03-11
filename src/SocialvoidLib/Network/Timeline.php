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
    use SocialvoidLib\Classes\Converter;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Internal\FollowerDataNotFound;
    use SocialvoidLib\Exceptions\Internal\UserTimelineNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PostNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPostTextException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Post;

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
         * @throws InvalidSearchMethodException
         * @throws FollowerDataNotFound
         * @throws UserTimelineNotFoundException
         * @throws PostNotFoundException
         * @throws InvalidPostTextException
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
                $PostObject->ID, $FollowerData->FollowersIDs
            );

            return $PostObject;
        }
    }
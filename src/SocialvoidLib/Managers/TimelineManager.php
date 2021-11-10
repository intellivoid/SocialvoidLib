<?php

    /** @noinspection PhpUnused */

    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Managers;

    use BackgroundWorker\Exceptions\ServerNotReachableException;
    use Exception;
    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\SearchMethods\TimelineSearchMethod;
    use SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Exceptions\GenericInternal\BackgroundWorkerNotEnabledException;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\DisplayPictureException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSlaveHashException;
    use SocialvoidLib\Exceptions\GenericInternal\ServiceJobException;
    use SocialvoidLib\Exceptions\GenericInternal\UserHasInvalidSlaveHashException;
    use SocialvoidLib\Exceptions\Internal\UserTimelineNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Objects\Standard\TimelineState;
    use SocialvoidLib\Objects\Timeline;
    use SocialvoidLib\Objects\User;
    use SocialvoidLib\SocialvoidLib;
    use ZiProto\ZiProto;

    /**
     * Class TimelineManager
     * @package SocialvoidLib\Managers
     */
    class TimelineManager
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * TimelineManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Retrieves an existing timeline, creates one if it doesn't exist
         *
         * @param User $user
         * @return Timeline
         * @throws DatabaseException
         * @throws InvalidSlaveHashException
         * @throws UserHasInvalidSlaveHashException
         * @throws UserTimelineNotFoundException
         */
        public function retrieveTimeline(User $user): Timeline
        {
            try
            {
                return $this->getTimeline($user);
            }
            catch(UserTimelineNotFoundException $e)
            {
                $this->createTimeline($user);
            }

            return $this->getTimeline($user);
        }

        /**
         * Gets the current timeline state
         *
         * @param string $search_method
         * @param string $value
         * @return TimelineState
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws UserTimelineNotFoundException
         */
        public function getTimelineState(string $search_method, string $value): TimelineState
        {
            switch($search_method)
            {
                case TimelineSearchMethod::ByUserId:
                case TimelineSearchMethod::ById:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = (int)$value;
                    break;

                default:
                    throw new InvalidSearchMethodException('The search method is not applicable to getTimelineState()', $search_method, $value);
            }

            $Query = QueryBuilder::select('peer_timelines', [
                'new_posts',
                'last_updated_timestamp',
            ], $search_method, $value);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new UserTimelineNotFoundException();
                }
                else
                {
                    $TimelineState = new TimelineState();
                    $TimelineState->TimelinePostsCount = (int)$Row['new_posts'];
                    $TimelineState->TimelineLastUpdated = (int)$Row['last_updated_timestamp'];

                    return $TimelineState;
                }
            }
            else
            {
                throw new DatabaseException(
                    'There was an error while trying retrieve the user timeline state from the network',
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Creates a new timeline for a user
         *
         * @param User $user
         * @throws DatabaseException
         * @throws UserHasInvalidSlaveHashException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function createTimeline(User $user): void
        {
            $Query = QueryBuilder::insert_into('peer_timelines', [
                'user_id' => (int)$user->ID,
                'post_chunks' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                'new_posts' => 0,
                'last_updated_timestamp' => (int)time(),
                'created_timestamp' => (int)time()
            ]);

            try
            {
                $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer($user->SlaveServer);
            }
            catch (InvalidSlaveHashException $e)
            {
                throw new UserHasInvalidSlaveHashException();
            }

            $QueryResults = $SelectedSlave->getConnection()->query($Query);
            if($QueryResults == false)
            {
                throw new DatabaseException('There was an error while trying to create user timeline',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
        }

        /**
         * Returns an existing timeline from the database
         *
         * @param User $user
         * @return Timeline
         * @throws DatabaseException
         * @throws UserTimelineNotFoundException
         * @throws InvalidSlaveHashException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function getTimeline(User $user): Timeline
        {
            $Query = QueryBuilder::select('peer_timelines', [
                'user_id',
                'post_chunks',
                'new_posts',
                'last_updated_timestamp',
                'created_timestamp'
            ], 'user_id', (int)$user->ID);
            $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer($user->SlaveServer);
            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new UserTimelineNotFoundException();
                }
                else
                {
                    $Row['post_chunks'] = ZiProto::decode($Row['post_chunks']);
                    $timeline = Timeline::fromArray($Row);
                    $timeline->SlaveHash = $SelectedSlave->MysqlServerPointer->HashPointer;

                    return $timeline;
                }
            }
            else
            {
                throw new DatabaseException(
                    'There was an error while trying retrieve the user timeline from the network',
                    $Query, $SelectedSlave->getConnection()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Updates an existing timeline record on the database
         *
         * @param Timeline $timeline
         * @throws DatabaseException
         * @throws UserTimelineNotFoundException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function updateTimeline(Timeline $timeline): void
        {
            $Query = QueryBuilder::update('peer_timelines', [
                'post_chunks' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($timeline->PostChunks)),
                'new_posts' => (int)$timeline->NewPosts,
                'last_updated_timestamp' => (int)time()
            ], 'user_id', (int)$timeline->UserID);

            try
            {
                $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer($timeline->SlaveHash);
            }
            catch (InvalidSlaveHashException $e)
            {
                throw new UserTimelineNotFoundException();
            }

            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            if($QueryResults == false)
            {
                throw new DatabaseException(
                    'There was an error while trying to update the timeline',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
        }

        /**
         * Distributes a post to the array of followers
         *
         * @param User $user
         * @param string $post_id
         * @param int $utilization
         * @param bool $skip_errors
         * @throws BackgroundWorkerNotEnabledException
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws InvalidSlaveHashException
         * @throws ServerNotReachableException
         * @throws UserHasInvalidSlaveHashException
         * @throws UserTimelineNotFoundException
         * @throws CacheException
         * @throws DisplayPictureException
         * @throws ServiceJobException
         * @throws DocumentNotFoundException
         * @throws PeerNotFoundException
         */
        public function distributePost(User $user, string $post_id, int $utilization=15, bool $skip_errors=true): void
        {
            // If background worker is enabled, split the query into multiple workers to speed up the process
            if(Utilities::getBoolDefinition('SOCIALVOID_LIB_BACKGROUND_WORKER_ENABLED'))
            {
                $this->socialvoidLib->getServiceJobManager()->getTimelineJobs()->distributeTimelinePosts(
                    $user, $post_id, $utilization, $skip_errors
                );
            }
            else
            {
                $FollowersCount = $this->socialvoidLib->getRelationStateManager()->getFollowersCount($user);

                // First distribute the post to the author
                $Timeline = $this->socialvoidLib->getTimelineManager()->retrieveTimeline($user);
                $Timeline->addPost($post_id);
                $this->socialvoidLib->getTimelineManager()->updateTimeline($Timeline);

                // Do it chunk(s) to avoid memory crashes
                if($FollowersCount > 0)
                {
                    $JobWeight = Utilities::calculateSplitJobWeight($FollowersCount,  500, 100);
                    $current_offset = 0;

                    foreach($JobWeight as $value)
                    {
                        $FollowerIds = $this->socialvoidLib->getRelationStateManager()->getFollowers($user, $value, $current_offset);
                        $QueryResults = [];
                        foreach($FollowerIds as $followerId)
                            $QueryResults[$followerId] = UserSearchMethod::ById;
                        $ResolvedFollowers = $this->socialvoidLib->getPeerManager()->getMultipleUsers($QueryResults, true, 15);


                        foreach($ResolvedFollowers as $user)
                        {
                            try
                            {
                                $Timeline = $this->socialvoidLib->getTimelineManager()->retrieveTimeline($user);
                                $Timeline->addPost($post_id);
                                $this->socialvoidLib->getTimelineManager()->updateTimeline($Timeline);
                            }
                            catch(Exception $e)
                            {
                                if($skip_errors == false)
                                    throw $e;
                            }
                        }

                        $current_offset += $value;
                    }
                }
            }
        }

        /**
         * Removes multiple posts from a timeline and reconstructs the chunks
         *
         * @param User $user
         * @param array $post_ids
         * @param bool $skip_errors
         * @throws BackgroundWorkerNotEnabledException
         * @throws DatabaseException
         * @throws InvalidSlaveHashException
         * @throws ServerNotReachableException
         * @throws UserHasInvalidSlaveHashException
         * @throws UserTimelineNotFoundException
         */
        public function removePosts(User $user, array $post_ids, bool $skip_errors=true): void
        {
            if(Utilities::getBoolDefinition('SOCIALVOID_LIB_BACKGROUND_WORKER_ENABLED'))
            {
                $this->socialvoidLib->getServiceJobManager()->getTimelineJobs()->removeTimelinePosts(
                    $user, $post_ids, $skip_errors
                );
            }
            else
            {
                try
                {
                    $timeline = $this->retrieveTimeline($user);

                    foreach($post_ids as $id)
                    {
                        $timeline->removePost($id);
                    }

                    $this->updateTimeline($timeline);

                }
                catch(Exception $e)
                {
                    if($skip_errors == false)
                        throw $e;
                }
            }
        }
    }
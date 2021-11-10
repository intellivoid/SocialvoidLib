<?php

    /** @noinspection PhpUnused */


    namespace SocialvoidLib\ServiceJobs\Jobs;

    use BackgroundWorker\Exceptions\ServerNotReachableException;
    use Exception;
    use SocialvoidLib\Abstracts\JobPriority;
    use SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod;
    use SocialvoidLib\Abstracts\Types\JobType;
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
    use SocialvoidLib\Objects\User;
    use SocialvoidLib\ServiceJobs\ServiceJobQuery;
    use SocialvoidLib\ServiceJobs\ServiceJobResults;
    use SocialvoidLib\SocialvoidLib;
    use ZiProto\ZiProto;

    /**
     * Class ResolveUsers
     * @package SocialvoidLib\ServiceJobs\Jobs
     */
    class TimelineJobs
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * UserJobs constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Constructs a job that resolves multiple users and returns their results
         *
         * @param User $user
         * @param string $post_id
         * @param int $utilization
         * @param bool $skip_errors
         * @throws BackgroundWorkerNotEnabledException
         * @throws DatabaseException
         * @throws ServerNotReachableException
         * @throws InvalidSlaveHashException
         * @throws UserHasInvalidSlaveHashException
         * @throws UserTimelineNotFoundException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function distributeTimelinePosts(User $user, string $post_id, int $utilization=100, bool $skip_errors=False): void
        {
            /**
             * This method now determines how many followers the post must be distributed to, then calculates the job
             * weight, for example if there are two workers and there are 100 users to distribute to. The fair amount of
             * weight for each worker will be 50, so each worker will distribute the post 50 users while resolving each
             * user. This method is faster as it doesn't need to pre-resolve the user before distribution but instead
             * tells the worker how to retrieve the users by a limit and offset identifier
             */

            $ServiceJobQueries = [];
            $FollowersCount = $this->socialvoidLib->getRelationStateManager()->getFollowersCount($user);

            if($FollowersCount > 0)
            {
                $JobWeight = Utilities::calculateSplitJobWeight($FollowersCount,  Utilities::getIntDefinition("SOCIALVOID_LIB_BACKGROUND_UPDATE_WORKERS"), $utilization);
                $current_offset = 0;

                foreach($JobWeight as $value)
                {
                    $ServiceJobQuery = new ServiceJobQuery();
                    $ServiceJobQuery->setJobType(JobType::DistributeTimelinePost);
                    $ServiceJobQuery->setJobPriority(JobPriority::Normal);
                    $ServiceJobQuery->setJobData([
                        0x000 => $skip_errors,
                        0x001 => $post_id,
                        0x002 => $user->toArray(),
                        0x003 => $current_offset, // Offset
                        0x004 => $value // Limit
                    ]);
                    $ServiceJobQuery->generateJobID();
                    $ServiceJobQueries[] = $ServiceJobQuery;
                    $current_offset += $value;
                }
            }

            // First distribute the post to the author
            $Timeline = $this->socialvoidLib->getTimelineManager()->retrieveTimeline($user);
            $Timeline->addPost($post_id);
            $this->socialvoidLib->getTimelineManager()->updateTimeline($Timeline);

            if($FollowersCount == 0)
                return;

            // Prepare the BackgroundWorker for the jobs
            $this->socialvoidLib->getBackgroundWorker()->getClient()->getGearmanClient()->clearCallbacks();
            $context_id = JobType::DistributeTimelinePost . "_" . (int)time();

            // Add the tasks
            foreach($ServiceJobQueries as $job)
            {
                // TODO: Respect the priority rule
                $this->socialvoidLib->getBackgroundWorker()->getClient()->getGearmanClient()->addTaskBackground(
                    Utilities::determineJobClass(JobType::DistributeTimelinePost),
                    ZiProto::encode($job->toArray()), $context_id
                );
            }

            $this->socialvoidLib->getBackgroundWorker()->getClient()->getGearmanClient()->runTasks();
        }

        /**
         * Processes the user resolve query
         *
         * @param ServiceJobQuery $serviceJobQuery
         * @return ServiceJobResults
         * @throws BackgroundWorkerNotEnabledException
         * @throws DatabaseException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @throws CacheException
         * @throws DisplayPictureException
         * @throws InvalidSearchMethodException
         * @throws DocumentNotFoundException
         * @throws PeerNotFoundException
         */
        public function processDistributeTimelinePost(ServiceJobQuery $serviceJobQuery): ServiceJobResults
        {
            $ServiceJobResults = ServiceJobResults::fromServiceJobQuery($serviceJobQuery);
            $User = User::fromArray($serviceJobQuery->getJobData()[0x002]);

            // Resolve the followers to distribute the post to
            $FollowerIds = $this->socialvoidLib->getRelationStateManager()->getFollowers(
                $User,
                (int)$serviceJobQuery->getJobData()[0x004], // Limit
                (int)$serviceJobQuery->getJobData()[0x003]  // Offset
            );

            $QueryResults = [];
            foreach($FollowerIds as $followerId)
                $QueryResults[$followerId] = UserSearchMethod::ById;
            $ResolvedFollowers = $this->socialvoidLib->getPeerManager()->getMultipleUsers($QueryResults, true, 15);

            foreach($ResolvedFollowers as $user)
            {
                try
                {
                    $Timeline = $this->socialvoidLib->getTimelineManager()->retrieveTimeline($user);
                    $Timeline->addPost($serviceJobQuery->getJobData()[0x001]);
                    $this->socialvoidLib->getTimelineManager()->updateTimeline($Timeline);
                }
                catch(Exception $e)
                {
                    // Throw an error if skipping errors it not an option
                    if($serviceJobQuery->getJobData()[0x000] == false)
                    {
                        $ServiceJobResults->setSuccess(false);
                    }

                    // Set the error anyways for troubleshooting purposes
                    $ServiceJobResults->setJobError(new ServiceJobException(
                        "There was an error while trying to resolve the distribution to the timeline",
                        $serviceJobQuery, $e
                    ));
                }
            }

            if($ServiceJobResults->getJobError() !== null)
                $ServiceJobResults->setSuccess(true);

            return $ServiceJobResults;
        }

        /**
         * Constructs a job that removes the requested Post IDs from the
         *
         * @param User $user
         * @param array $post_ids
         * @param bool $skip_errors
         * @throws BackgroundWorkerNotEnabledException
         * @throws ServerNotReachableException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function removeTimelinePosts(User $user, array $post_ids, bool $skip_errors=False): void
        {
            $ServiceJobQuery = new ServiceJobQuery();
            $ServiceJobQuery->setJobType(JobType::RemoveTimelinePosts);
            $ServiceJobQuery->setJobPriority(JobPriority::Normal);
            $ServiceJobQuery->setJobData([
                0x000 => $skip_errors,
                0x001 => $user->toArray(),
                0x002 => $post_ids
            ]);
            $ServiceJobQuery->generateJobID();
            $ServiceJobQueries[] = $ServiceJobQuery;

            // Prepare the BackgroundWorker for the jobs
            $this->socialvoidLib->getBackgroundWorker()->getClient()->getGearmanClient()->clearCallbacks();
            $context_id = JobType::DistributeTimelinePost . "_" . (int)time();

            $this->socialvoidLib->getBackgroundWorker()->getClient()->getGearmanClient()->doBackground(
                Utilities::determineJobClass(JobType::RemoveTimelinePosts),
                ZiProto::encode($ServiceJobQuery->toArray()), $context_id
            );
        }

        /**
         * Processes the removal of multiple posts on Timeline
         *
         * @param ServiceJobQuery $serviceJobQuery
         * @return ServiceJobResults
         */
        public function processRemoveTimelinePosts(ServiceJobQuery $serviceJobQuery): ServiceJobResults
        {
            $ServiceJobResults = ServiceJobResults::fromServiceJobQuery($serviceJobQuery);

            try
            {
                $Timeline = $this->socialvoidLib->getTimelineManager()->retrieveTimeline(
                    User::fromArray($serviceJobQuery->getJobData()[0x001])
                );

            }
            catch(Exception $e)
            {
                // Throw an error if skipping errors it not an option
                if($serviceJobQuery->getJobData()[0x000] == false)
                {
                    $ServiceJobResults->setSuccess(false);
                }

                // Set the error anyways for troubleshooting purposes
                $ServiceJobResults->setJobError(new ServiceJobException(
                    "There was an error while trying to retrieve the timeline",
                    $serviceJobQuery, $e
                ));

                return $ServiceJobResults;
            }


            foreach($serviceJobQuery->getJobData()[0x002] as $post_id)
            {
                $Timeline->removePost($post_id);
            }

            try
            {
                $this->socialvoidLib->getTimelineManager()->updateTimeline($Timeline);

            }
            catch(Exception $e)
            {
                // Throw an error if skipping errors it not an option
                if($serviceJobQuery->getJobData()[0x000] == false)
                {
                    $ServiceJobResults->setSuccess(false);
                }

                // Set the error anyways for troubleshooting purposes
                $ServiceJobResults->setJobError(new ServiceJobException(
                    "There was an error while trying to update the timeline",
                    $serviceJobQuery, $e
                ));

                return $ServiceJobResults;
            }

            if($ServiceJobResults->getJobError() !== null)
                $ServiceJobResults->setSuccess(true);

            return $ServiceJobResults;
        }
    }
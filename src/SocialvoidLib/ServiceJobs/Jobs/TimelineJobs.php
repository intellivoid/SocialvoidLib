<?php

    /** @noinspection PhpUnused */


    namespace SocialvoidLib\ServiceJobs\Jobs;

    use BackgroundWorker\Exceptions\ServerNotReachableException;
    use Exception;
    use SocialvoidLib\Abstracts\JobPriority;
    use SocialvoidLib\Abstracts\Types\JobType;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Exceptions\GenericInternal\BackgroundWorkerNotEnabledException;
    use SocialvoidLib\Exceptions\GenericInternal\ServiceJobException;
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
         * @param string $post_id
         * @param array $user_ids
         * @param int $utilization
         * @param bool $skip_errors
         * @throws BackgroundWorkerNotEnabledException
         * @throws ServerNotReachableException
         */
        public function distributeTimelinePosts(User $user, string $post_id, int $utilization=100, bool $skip_errors=False): void
        {
            $ServiceJobQueries = [];
            $FollowersCount = $this->socialvoidLib->getRelationStateManager()->getFollowersCount($user);

            foreach(Utilities::splitJobWeight(
                $user_ids, Utilities::getIntDefinition("SOCIALVOID_LIB_BACKGROUND_UPDATE_WORKERS"), false, $utilization) as $chunk)
            {
                $ServiceJobQuery = new ServiceJobQuery();
                $ServiceJobQuery->setJobType(JobType::DistributeTimelinePost);
                $ServiceJobQuery->setJobPriority(JobPriority::Normal);
                $ServiceJobQuery->setJobData([
                    0x000 => $skip_errors,
                    0x001 => $post_id,
                    0x002 => $chunk
                ]);
                $ServiceJobQuery->generateJobID();

                $ServiceJobQueries[] = $ServiceJobQuery;
            }

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
         */
        public function processDistributeTimelinePost(ServiceJobQuery $serviceJobQuery): ServiceJobResults
        {
            $ServiceJobResults = ServiceJobResults::fromServiceJobQuery($serviceJobQuery);
            foreach($serviceJobQuery->getJobData()[0x002] as $user_id)
            {
                try
                {
                    $Timeline = $this->socialvoidLib->getTimelineManager()->retrieveTimeline($user_id);
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
                        "There was an error while trying to resolve the distribution to the timeline '$user_id'",
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
         * @param int $user_id
         * @param array $post_ids
         * @param bool $skip_errors
         * @throws BackgroundWorkerNotEnabledException
         * @throws ServerNotReachableException
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
<?php


    namespace SocialvoidLib\ServiceJobs\Jobs;


    use BackgroundWorker\Exceptions\ServerNotReachableException;
    use Exception;
    use GearmanTask;
    use SocialvoidLib\Abstracts\JobPriority;
    use SocialvoidLib\Abstracts\Types\JobType;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Exceptions\GenericInternal\BackgroundWorkerNotEnabledException;
    use SocialvoidLib\Exceptions\GenericInternal\ServiceJobException;
    use SocialvoidLib\Objects\Post;
    use SocialvoidLib\ServiceJobs\ServiceJobQuery;
    use SocialvoidLib\ServiceJobs\ServiceJobResults;
    use SocialvoidLib\SocialvoidLib;
    use ZiProto\ZiProto;

    /**
     * Class PostJobs
     * @package SocialvoidLib\ServiceJobs\Jobs
     */
    class PostJobs
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * PostJobs constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Resolves an array of posts
         *
         * @param array $query
         * @param int $utilization
         * @param bool $skip_errors
         * @return Post[]
         * @throws BackgroundWorkerNotEnabledException
         * @throws ServiceJobException
         * @throws ServerNotReachableException
         * @noinspection DuplicatedCode
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function resolvePosts(array $query, int $utilization=100, bool $skip_errors=False): array
        {
            $ServiceJobQueries = [];

            foreach(Utilities::splitJobWeight(
                $query, Utilities::getIntDefinition("SOCIALVOID_LIB_BACKGROUND_QUERY_WORKERS"), true, $utilization) as $chunk)
            {
                $ServiceJobQuery = new ServiceJobQuery();
                $ServiceJobQuery->setJobType(JobType::ResolvePosts);
                $ServiceJobQuery->setJobPriority(JobPriority::High);
                $ServiceJobQuery->setJobData([
                    0x000 => $skip_errors,
                    0x001 => $chunk
                ]);
                $ServiceJobQuery->generateJobID();

                $ServiceJobQueries[] = $ServiceJobQuery;
            }

            // Prepare the BackgroundWorker for the jobs
            $this->socialvoidLib->getBackgroundWorker()->getClient()->getGearmanClient()->clearCallbacks();

            /** @var ServiceJobResults $results */
            $results = [];
            $context_id = JobType::ResolvePosts . "_" . (int)time();

            // Handles the job callbacks
            $this->socialvoidLib->getBackgroundWorker()->getClient()->getGearmanClient()->setCompleteCallback(
                function(GearmanTask $task, $context) use (&$results, &$context_id)
                {
                    if($context == $context_id)
                    {
                        if($task->data() == null)
                            return;

                        $results[] = ServiceJobResults::fromArray(ZiProto::decode($task->data()));
                    }
                }
            );

            // Add the tasks
            foreach($ServiceJobQueries as $job)
            {
                // TODO: Respect the priority rule
                $this->socialvoidLib->getBackgroundWorker()->getClient()->getGearmanClient()->addTask(
                    Utilities::determineJobClass(JobType::ResolvePosts),
                    ZiProto::encode($job->toArray()), $context_id
                );
            }

            $this->socialvoidLib->getBackgroundWorker()->getClient()->getGearmanClient()->runTasks();

            $return_results = [];

            /** @var ServiceJobResults $result */
            foreach($results as $result)
            {
                if($result->isSuccess() == false && $skip_errors == false)
                {
                    if($result->getJobError() == null)
                        throw new ServiceJobException("The job query failed but no error was returned");

                    throw $result->getJobError();
                }

                if($result->getJobResults() !== null)
                {
                    foreach($result->getJobResults() as $jobResult)
                        $return_results[] = Post::fromArray($jobResult);
                }
            }

            return $return_results;
        }

        /**
         * Processes the post resolve query
         *
         * @param ServiceJobQuery $serviceJobQuery
         * @return ServiceJobResults
         */
        public function processResolvePosts(ServiceJobQuery $serviceJobQuery): ServiceJobResults
        {
            $PostResults = [];
            $ServiceJobResults = ServiceJobResults::fromServiceJobQuery($serviceJobQuery);
            $ServiceJobResults->setSuccess(true);

            foreach($serviceJobQuery->getJobData()[0x001] as $query_value => $search_method)
            {
                try
                {
                    $PostResults[] = $this->socialvoidLib->getPostsManager()->getPost(
                        $search_method, $query_value
                    )->toArray();
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
                        "There was an error while trying to resolve the post '$query_value' by '$search_method'",
                        $serviceJobQuery, $e
                    ));

                    if($serviceJobQuery->getJobData()[0x000] == false)
                        return $ServiceJobResults;
                }
            }

            $ServiceJobResults->setJobResults($PostResults);


            return $ServiceJobResults;
        }
    }
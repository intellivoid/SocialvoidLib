<?php


    namespace SocialvoidLib\ServiceJobs;

    use BackgroundWorker\Exceptions\ServerNotReachableException;
    use GearmanJob;
    use SocialvoidLib\Abstracts\Types\JobType;
    use SocialvoidLib\Exceptions\GenericInternal\BackgroundWorkerNotEnabledException;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\DisplayPictureException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\GenericInternal\ServiceJobException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidFileNameException;
    use SocialvoidLib\SocialvoidLib;
    use ZiProto\ZiProto;

    // +------------------+
    // |       ___        |
    // |   _  (,~ |   _   |
    // |  (____/  |____)  |
    // |  |||||    |||||  |
    // |  |||||    |||||  |
    // |  |||||\  /|||||  |
    // |  |||'//\/\\`|||  |
    // |  |' m' /\ `m `|  |
    // |       /||\       |
    //  \_              _/
    //    `-----92-KSR-'

    /**
     * Class ServiceJobHandler
     * @package SocialvoidLib\ServiceJobs
     */
    class ServiceJobHandler
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * ServiceJobHandler constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Handles the incoming gearman job
         *
         * @param GearmanJob $job
         * @return ServiceJobResults
         * @throws ServiceJobException
         * @throws ServerNotReachableException
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws InvalidSearchMethodException
         * @throws DocumentNotFoundException
         * @throws PeerNotFoundException
         * @throws InvalidFileNameException
         */
        public function handle(GearmanJob $job): ServiceJobResults
        {
            $ServiceJobQuery = ServiceJobQuery::fromArray(ZiProto::decode($job->workload()));
            switch($ServiceJobQuery->getJobType())
            {
                case JobType::ResolveUsers:
                    return $this->socialvoidLib->getServiceJobManager()->getUserJobs()->processResolveUsers($ServiceJobQuery);

                case JobType::ResolvePosts:
                    return $this->socialvoidLib->getServiceJobManager()->getPostJobs()->processResolvePosts($ServiceJobQuery);

                case JobType::DistributeTimelinePost:
                    return $this->socialvoidLib->getServiceJobManager()->getTimelineJobs()->processDistributeTimelinePost($ServiceJobQuery);

                case JobType::RemoveTimelinePosts:
                    return $this->socialvoidLib->getServiceJobManager()->getTimelineJobs()->processRemoveTimelinePosts($ServiceJobQuery);

                default:
                    $ServiceJobResults = ServiceJobResults::fromServiceJobQuery($ServiceJobQuery);
                    $ServiceJobResults->setSuccess(false);
                    $ServiceJobResults->setJobError(new ServiceJobException(
                        "The job type '" . $ServiceJobQuery->getJobType() . "' is not supported", $ServiceJobQuery));
                    return $ServiceJobResults;
            }
        }
    }
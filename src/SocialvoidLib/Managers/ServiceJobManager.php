<?php


    namespace SocialvoidLib\Managers;

    use SocialvoidLib\ServiceJobs\Jobs\PostJobs;
    use SocialvoidLib\ServiceJobs\Jobs\TimelineJobs;
    use SocialvoidLib\ServiceJobs\Jobs\UserJobs;
    use SocialvoidLib\ServiceJobs\ServiceJobHandler;
    use SocialvoidLib\SocialvoidLib;

    /**
     * Class ServiceJobManager
     * @package SocialvoidLib\Managers
     */
    class ServiceJobManager
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * @var ServiceJobHandler
         */
        private ServiceJobHandler $serviceJobHandler;

        /**
         * @var UserJobs
         */
        private UserJobs $userJobs;

        /**
         * @var TimelineJobs
         */
        private TimelineJobs $TimelineJobs;

        /**
         * @var PostJobs
         */
        private PostJobs $postJobs;

        /**
         * ServiceJobManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
            $this->serviceJobHandler = new ServiceJobHandler($this->socialvoidLib);
            $this->userJobs = new UserJobs($this->socialvoidLib);
            $this->postJobs = new PostJobs($this->socialvoidLib);
            $this->TimelineJobs = new TimelineJobs($this->socialvoidLib);
        }

        /**
         * @return SocialvoidLib
         */
        public function getSocialvoidLib(): SocialvoidLib
        {
            return $this->socialvoidLib;
        }

        /**
         * @return ServiceJobHandler
         */
        public function getServiceJobHandler(): ServiceJobHandler
        {
            return $this->serviceJobHandler;
        }

        /**
         * @return UserJobs
         */
        public function getUserJobs(): UserJobs
        {
            return $this->userJobs;
        }

        /**
         * @return TimelineJobs
         */
        public function getTimelineJobs(): TimelineJobs
        {
            return $this->TimelineJobs;
        }

        /**
         * @return PostJobs
         */
        public function getPostJobs(): PostJobs
        {
            return $this->postJobs;
        }
    }
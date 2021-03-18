<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\ServiceJobs;

    use SocialvoidLib\Abstracts\JobPriority;
    use SocialvoidLib\Classes\Utilities;

    /**
     * Class ServiceJobQuery
     * @package SocialvoidLib\ServiceJobs
     */
    class ServiceJobQuery
    {
        /**
         * @var string
         */
        private $JobType;

        /**
         * @var string|JobPriority
         */
        private $JobPriority;

        /**
         * The job data to be executed
         *
         * @var array
         */
        private $JobData;

        /**
         * The Job ID to keep track of the job success and error rates
         *
         * @var string
         */
        private $JobID;

        /**
         * Generates a new Job ID and returns the ID
         *
         * @return string
         */
        public function generateJobID(): string
        {
            $this->JobID = Utilities::generateJobID($this->toArray(), (int)time());
            return $this->JobID;
        }

        /**
         * Returns the job type
         *
         * @return string
         */
        public function getJobType(): string
        {
            return $this->JobType;
        }

        /**
         * Returns the job Priority
         *
         * @return string|JobPriority
         */
        public function getJobPriority(): string
        {
            return $this->JobPriority;
        }

        /**
         * Returns the Job Data
         *
         * @return array
         */
        public function getJobData(): array
        {
            return $this->JobData;
        }

        /**
         * Sets the job data
         *
         * @param array $data
         */
        public function setJobData(array $data): void
        {
            $this->JobData = $data;
        }

        /**
         * Returns the Job ID
         *
         * @return string
         */
        public function getJobID(): string
        {
            return $this->JobID;
        }

        /**
         * Returns a array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                0x001 => $this->JobType,
                0x002 => $this->JobPriority,
                0x003 => $this->JobData,
                0x004 => $this->JobID
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return ServiceJobQuery
         */
        public static function fromArray(array $data): ServiceJobQuery
        {
            $ServiceJobObject = new ServiceJobQuery();

            if(isset($data[0x001]))
                $ServiceJobObject->JobType = $data[0x001];

            if(isset($data[0x002]))
                $ServiceJobObject->JobPriority = $data[0x002];

            if(isset($data[0x003]))
                $ServiceJobObject->JobData = $data[0x003];

            if(isset($data[0x004]))
                $ServiceJobObject->JobID = $data[0x004];

            return $ServiceJobObject;
        }

        /**
         * @param string $JobType
         */
        public function setJobType(string $JobType): void
        {
            $this->JobType = $JobType;
        }

        /**
         * @param JobPriority|string $JobPriority
         */
        public function setJobPriority($JobPriority): void
        {
            $this->JobPriority = $JobPriority;
        }
    }
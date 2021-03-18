<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */


    namespace SocialvoidLib\ServiceJobs;


    use SocialvoidLib\Exceptions\GenericInternal\ServiceJobException;

    /**
     * Class ServiceJobResults
     * @package SocialvoidLib\ServiceJobs
     */
    class ServiceJobResults
    {
        /**
         * @var string
         */
        private $JobType;

        /**
         * Indicates if the job was a success or not
         *
         * @var bool
         */
        private $Success;

        /**
         * The results of the job
         *
         * @var array|null
         */
        private $JobResults;

        /**
         * The exception thrown if an error is raised
         *
         * @var ServiceJobException|null
         */
        private $JobError;

        /**
         * The Job ID to keep track of the job success and error rates
         *
         * @var string|null
         */
        private $JobID;

        /**
         * @return string
         */
        public function getJobType(): string
        {
            return $this->JobType;
        }

        /**
         * @param string $JobType
         */
        public function setJobType(string $JobType): void
        {
            $this->JobType = $JobType;
        }

        /**
         * @return bool
         */
        public function isSuccess(): bool
        {
            return $this->Success;
        }

        /**
         * @param bool $Success
         */
        public function setSuccess(bool $Success): void
        {
            $this->Success = $Success;
        }

        /**
         * @return array|null
         */
        public function getJobResults(): ?array
        {
            return $this->JobResults;
        }

        /**
         * @param array $JobResults
         */
        public function setJobResults(array $JobResults): void
        {
            $this->JobResults = $JobResults;
        }

        /**
         * @return ServiceJobException|null
         */
        public function getJobError(): ?ServiceJobException
        {
            return $this->JobError;
        }

        /**
         * @param ServiceJobException|null $JobError
         */
        public function setJobError(?ServiceJobException $JobError): void
        {
            $this->JobError = $JobError;
        }

        /**
         * @return string|null
         */
        public function getJobID(): ?string
        {
            return $this->JobID;
        }

        /**
         * @param string|null $JobID
         */
        public function setJobID(?string $JobID): void
        {
            $this->JobID = $JobID;
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                0x001 => $this->JobType,
                0x002 => $this->Success,
                0x003 => ($this->JobError == null ? null : serialize($this->JobError)),
                0x004 => ($this->JobResults == null ? null : $this->JobResults),
                0x005 => ($this->JobID == null ? null : $this->JobID)
            ];
        }

        /**
         * Constructs object based off a job query
         *
         * @param ServiceJobQuery $serviceJobQuery
         * @return ServiceJobResults
         */
        public static function fromServiceJobQuery(ServiceJobQuery $serviceJobQuery): ServiceJobResults
        {
            $ServiceJobResults = new ServiceJobResults();
            $ServiceJobResults->setSuccess(false);
            $ServiceJobResults->setJobType($serviceJobQuery->getJobType());
            $ServiceJobResults->setJobID($serviceJobQuery->getJobID());

            return $ServiceJobResults;
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return ServiceJobResults
         */
        public static function fromArray(array $data): ServiceJobResults
        {
            $ServiceJobResultsObject = new ServiceJobResults();

            if(isset($data[0x001]))
                $ServiceJobResultsObject->JobType = $data[0x001];

            if(isset($data[0x002]))
                $ServiceJobResultsObject->Success = $data[0x002];

            if(isset($data[0x003]))
                $ServiceJobResultsObject->JobError = ($data[0x003] == null ? null : unserialize($data[0x003]));

            if(isset($data[0x004]))
                $ServiceJobResultsObject->JobResults = $data[0x004];

            if(isset($data[0x005]))
                $ServiceJobResultsObject->JobID = $data[0x005];

            return $ServiceJobResultsObject;
        }
    }
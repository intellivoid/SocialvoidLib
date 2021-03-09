<?php


namespace SocialvoidLib\Service\Jobs\UserManager;


    use SocialvoidLib\Objects\User;

    /**
     * Class GetUserJobResults
     * @package SocialvoidLib\Service\Jobs\UserManager
     */
    class GetUserJobResults
    {
        /**
         * @var string|int
         */
        public $JobID;

        /**
         * @var User|null
         */
        public $User;

        /**
         * @return int|string
         */
        public function getJobID()
        {
            return $this->JobID;
        }

        /**
         * @return User|null
         */
        public function getUser(): ?User
        {
            return $this->User;
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                0x001 => $this->JobID,
                0x002 => ($this->User == null ? null : $this->User->toArray())
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return GetUserJobResults
         */
        public static function fromArray(array $data): GetUserJobResults
        {
            $GetUserJobResultsObject = new GetUserJobResults();

            if(isset($data[0x001]))
                $GetUserJobResultsObject->JobID = $data[0x001];

            if(isset($data[0x002]))
                $GetUserJobResultsObject->User = ($data[0x002] == null ? null : User::fromArray($data[0x002]));

            return $GetUserJobResultsObject;
        }
    }
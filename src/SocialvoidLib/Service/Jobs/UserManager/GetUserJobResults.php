<?php /** @noinspection PhpMissingFieldTypeInspection */

/*
 * Copyright (c) 2017-2021. Intellivoid Technologies
 *
 * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
 * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
 * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
 * must have a written permission from Intellivoid Technologies to do so.
 */

namespace SocialvoidLib\Service\Jobs\UserManager;


    use SocialvoidLib\Objects\User;
    use SocialvoidLib\ServiceJobs\Jobs\UserJobs;

    /**
     * Class GetUserJobResults
     * @package SocialvoidLib\Service\Jobs\UserManager
     *
     * @deprecated
     * @see UserJobs
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
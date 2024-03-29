<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    /** @noinspection PhpMissingFieldTypeInspection */


    namespace SocialvoidLib\Service\Jobs\UserManager;

    use SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\ServiceJobs\Jobs\UserJobs;

    /**
     * Class GetUserJob
     * @package SocialvoidLib\Service\Jobs\UserManager
     *
     * @deprecated
     * @see UserJobs
     */
    class GetUserJob
    {
        /**
         * The value to search by
         *
         * @var string|UserSearchMethod
         */
        public $SearchMethod;

        /**
         * The value to search for
         *
         * @var string|int|null
         */
        public $Value;

        /**
         * Job ID
         *
         * @var string|null
         */
        public $JobID = null;

        /**
         * GetUserJob constructor.
         * @param string|null $search_by
         * @param null $value
         * @return GetUserJob
         */
        public static function fromInput(string $search_by=null, $value=null): GetUserJob
        {
            $GetUserJobObject = new GetUserJob();
            $GetUserJobObject->SearchMethod = $search_by;
            $GetUserJobObject->Value = $value;
            $GetUserJobObject->JobID = Utilities::generateJobID($GetUserJobObject->toArray(), (int)time());

            return $GetUserJobObject;
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                0x001 => $this->SearchMethod,
                0x002 => $this->Value,
                0x003 => $this->JobID
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return GetUserJob
         */
        public static function fromArray(array $data): GetUserJob
        {
            $GetUserJobObject = new GetUserJob();

            if(isset($data[0x001]))
                $GetUserJobObject->SearchMethod = $data[0x001];

            if(isset($data[0x002]))
                $GetUserJobObject->Value = $data[0x002];

            if(isset($data[0x003]))
                $GetUserJobObject->JobID = $data[0x003];

            return $GetUserJobObject;
        }
    }
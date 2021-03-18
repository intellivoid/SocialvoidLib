<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Service\Jobs\Timeline;

    use SocialvoidLib\ServiceJobs\Jobs\TimelineJobs;

    /**
     * Class DistributePostJob
     * @package SocialvoidLib\Service\Jobs\Timeline
     *
     * @deprecated
     * @see TimelineJobs
     */
    class DistributePostJob
    {
        /**
         * @var int
         */
        public $PostID;

        /**
         * @var int[]
         */
        public $Followers;

        /**
         * Job ID
         *
         * @var string|null
         */
        public $JobID = null;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                0x001 => $this->PostID,
                0x002 => $this->Followers,
                0x003 => $this->JobID
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return DistributePostJob
         */
        public static function fromArray(array $data): DistributePostJob
        {
            $DistributePostJobObject = new DistributePostJob();

            if(isset($data[0x001]))
                $DistributePostJobObject->PostID = $data[0x001];

            if(isset($data[0x002]))
                $DistributePostJobObject->Followers = $data[0x002];

            if(isset($data[0x003]))
                $DistributePostJobObject->JobID = $data[0x003];

            return $DistributePostJobObject;
        }

    }
<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Service\Jobs\Timeline;

    use SocialvoidLib\ServiceJobs\Jobs\TimelineJobs;

    /**
     * Class DistributePostJobResults
     * @package SocialvoidLib\Service\Jobs\Timeline
     *
     * @deprecated
     * @see TimelineJobs
     */
    class DistributePostJobResults
    {
        /**
         * @var string|int
         */
        public $JobID;

        /**
         * @return int|string
         */
        public function getJobID()
        {
            return $this->JobID;
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                0x001 => $this->JobID
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return DistributePostJobResults
         */
        public static function fromArray(array $data): DistributePostJobResults
        {
            $distributePostJobResults = new DistributePostJobResults();

            if(isset($data[0x001]))
                $distributePostJobResults->JobID = $data[0x001];

            return $distributePostJobResults;
        }
    }
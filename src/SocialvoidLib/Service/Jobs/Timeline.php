<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Service\Jobs;


    use Exception;
    use GearmanJob;
    use SocialvoidLib\Service\Jobs\Timeline\DistributePostJob;
    use SocialvoidLib\Service\Jobs\Timeline\DistributePostJobResults;
    use SocialvoidService\SocialvoidService;
    use ZiProto\ZiProto;

    /**
     * Class Timeline
     * @package SocialvoidLib\Service\Jobs
     */
    class Timeline
    {
        /**
         * Distributes a post to the users timelines
         *
         * @param GearmanJob $job
         * @return string
         */
        public static function distributePost(GearmanJob $job): string
        {
            $DistributePostJob = DistributePostJob::fromArray(ZiProto::decode($job->workload()));

            try
            {
                SocialvoidService::getSocialvoidLib()->getTimelineManager()->distributePost(
                    $DistributePostJob->PostID, $DistributePostJob->Followers, false
                );
            }
            catch(Exception $e)
            {
                unset($e);
            }

            $Results = new DistributePostJobResults();
            $Results->JobID = $DistributePostJob->JobID;

            return ZiProto::encode($Results->toArray());
        }
    }
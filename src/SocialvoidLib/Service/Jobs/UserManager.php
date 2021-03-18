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
    use SocialvoidLib\Service\Jobs\UserManager\GetUserJob;
    use SocialvoidLib\Service\Jobs\UserManager\GetUserJobResults;
    use SocialvoidLib\ServiceJobs\Jobs\UserJobs;
    use SocialvoidService\SocialvoidService;
    use ZiProto\ZiProto;

    /**
     * Class ResolveUser
     * @package SocialvoidService\ServiceJobs
     *
     * @deprecated
     * @see UserJobs
     */
    class UserManager
    {

        /**
         * @param GearmanJob $job
         * @return string
         */
        public static function getUser(GearmanJob $job)
        {
            $getUserJob = GetUserJob::fromArray(ZiProto::decode($job->workload()));

            try
            {
                $User = SocialvoidService::getSocialvoidLib()->getUserManager()->getUser(
                    $getUserJob->SearchMethod, $getUserJob->Value
                );
            }
            catch(Exception $e)
            {
                $User = null;
            }

            $Results = new GetUserJobResults();
            $Results->User = $User;
            $Results->JobID = $getUserJob->JobID;

            return ZiProto::encode($Results->toArray());
        }
    }
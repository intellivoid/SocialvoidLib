<?php


    namespace SocialvoidLib\Service\Jobs;

    use Exception;
    use GearmanJob;
    use SocialvoidLib\Service\Jobs\UserManager\GetUserJob;
    use SocialvoidLib\Service\Jobs\UserManager\GetUserJobResults;
    use SocialvoidService\SocialvoidService;
    use ZiProto\ZiProto;

    /**
     * Class ResolveUser
     * @package SocialvoidService\ServiceJobs
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
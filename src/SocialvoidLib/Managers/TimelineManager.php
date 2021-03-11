<?php

    /** @noinspection PhpUnused */

    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Managers;


    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\SearchMethods\TimelineSearchMethod;
    use SocialvoidLib\Abstracts\StatusStates\TimelineState;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Exceptions\GenericInternal\BackgroundWorkerNotEnabledException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Internal\UserTimelineNotFoundException;
    use SocialvoidLib\Objects\Timeline;
    use SocialvoidLib\Service\Jobs\Timeline\DistributePostJob;
    use SocialvoidLib\SocialvoidLib;
    use ZiProto\ZiProto;

    /**
     * Class TimelineManager
     * @package SocialvoidLib\Managers
     */
    class TimelineManager
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * TimelineManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Retrieves an existing timeline, creates one if it doesn't exist
         *
         * @param int $user_id
         * @return Timeline
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws UserTimelineNotFoundException
         */
        public function retrieveTimeline(int $user_id): Timeline
        {
            try
            {
                return $this->getTimeline(TimelineSearchMethod::ByUserId, $user_id);
            }
            catch(UserTimelineNotFoundException $e)
            {
                $this->createTimeline($user_id);
            }

            return $this->getTimeline(TimelineSearchMethod::ByUserId, $user_id);
        }

        /**
         * Creates a new timeline for a user
         *
         * @param int $user_id
         * @throws DatabaseException
         */
        public function createTimeline(int $user_id): void
        {
            $Query = QueryBuilder::insert_into("user_timelines", [
                "user_id" => (int)$user_id,
                "state" => $this->socialvoidLib->getDatabase()->real_escape_string(TimelineState::Available),
                "post_chunks" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "new_posts" => 0,
                "last_updated_timestamp" => (int)time(),
                "created_timestamp" => (int)time()
            ]);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);
            if($QueryResults == false)
            {
                throw new DatabaseException("There was an error while trying to create user timeline",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Returns an existing timeline from the database
         *
         * @param string $search_method
         * @param string $value
         * @return Timeline
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws UserTimelineNotFoundException
         */
        public function getTimeline(string $search_method, string $value): Timeline
        {
            switch($search_method)
            {
                case TimelineSearchMethod::ByUserId:
                case TimelineSearchMethod::ById:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = (int)$value;
                    break;

                default:
                    throw new InvalidSearchMethodException("The search method is not applicable to getTimeline()", $search_method, $value);
            }

            $Query = QueryBuilder::select("user_timelines", [
                "id",
                "user_id",
                "state",
                "post_chunks",
                "new_posts",
                "last_updated_timestamp",
                "created_timestamp"
            ], $search_method, $value);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new UserTimelineNotFoundException();
                }
                else
                {
                    $Row["post_chunks"] = ZiProto::decode($Row["post_chunks"]);
                    return(Timeline::fromArray($Row));
                }
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying retrieve the user timeline from the network",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Updates an existing timeline record on the database
         *
         * @param Timeline $timeline
         * @throws DatabaseException
         */
        public function updateTimeline(Timeline $timeline): void
        {
            $Query = QueryBuilder::update("user_timelines", [
                "state" => $this->socialvoidLib->getDatabase()->real_escape_string($timeline->State),
                "post_chunks" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($timeline->PostChunks)),
                "new_posts" => (int)$timeline->NewPosts,
                "last_updated_timestamp" => (int)time()
            ], "id", (int)$timeline->ID);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults == false)
            {
                throw new DatabaseException(
                    "There was an error while trying to update the session",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Distributes a post to the array of followers
         *
         * @param int $post_id
         * @param array $followers
         * @param bool $service_jobs
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws UserTimelineNotFoundException
         * @throws BackgroundWorkerNotEnabledException
         */
        public function distributePost(int $post_id, array $followers, $service_jobs=True): void
        {
            // If background worker is enabled, split the query into multiple workers to speed up the process
            if(Utilities::getBoolDefinition("SOCIALVOID_LIB_BACKGROUND_WORKER_ENABLED") && $service_jobs == True)
            {
                $context_id = hash("crc32b", time());

                // Split the followers jobs
                $follower_chunks = [];
                if(count($followers) == 1)
                {
                    $follower_chunks[] = [$followers];
                }
                else
                {
                    $chunks_count = (int)round(count($followers) / Utilities::getIntDefinition("SOCIALVOID_LIB_BACKGROUND_WORKERS_AVAILABLE"));
                    $follower_chunks = array_chunk($followers, $chunks_count);
                }

                foreach($follower_chunks as $follower_chunk)
                {
                    $job_object = new DistributePostJob();
                    $job_object->PostID = $post_id;
                    $job_object->Followers = $follower_chunk;
                    $job_object->JobID = Utilities::generateJobID($job_object->toArray(), (int)time());

                    // Push each job to the background
                    $this->socialvoidLib->getBackgroundWorker()->getClient()->getGearmanClient()->addTaskBackground(
                        "distribute_post", ZiProto::encode($job_object->toArray()), "distribute_post_" . $context_id
                    );
                }

                $this->socialvoidLib->getBackgroundWorker()->getClient()->getGearmanClient()->runTasks();

            }
            else
            {
                foreach($followers as $user_id)
                {
                    $Timeline = $this->retrieveTimeline($user_id);
                    $Timeline->addPost($post_id);
                    $this->updateTimeline($Timeline);
                }
            }
        }
    }
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
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\Internal\LikeRecordNotFoundException;
    use SocialvoidLib\Objects\LikeRecord;
    use SocialvoidLib\SocialvoidLib;

    /**
     * Class LikesRecordManager
     * @package SocialvoidLib\Managers
     */
    class LikesRecordManager
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * LikesRecordManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Creates a like record if one doesn't already exist, or updates an existing one
         *
         * @param int $user_id
         * @param string $post_id
         * @throws DatabaseException
         */
        public function likeRecord(int $user_id, string $post_id)
        {
            try
            {
                $record = $this->getRecord($user_id, $post_id);
            }
            catch(LikeRecordNotFoundException $e)
            {
                $this->registerRecord($user_id, $post_id, true);
                return;
            }

            $record->Liked = true;
            $this->updateRecord($record);
        }

        /**
         * Creates a like record if one doesn't already exist, or updates an existing one
         *
         * @param int $user_id
         * @param string $post_id
         * @throws DatabaseException
         */
        public function unlikeRecord(int $user_id, string $post_id)
        {
            try
            {
                $record = $this->getRecord($user_id, $post_id);
            }
            catch(LikeRecordNotFoundException $e)
            {
                $this->registerRecord($user_id, $post_id, false);
                return;
            }

            $record->Liked = false;
            $this->updateRecord($record);
        }

        /**
         * Registers a new like record into the database
         *
         * @param int $user_id
         * @param string $post_id
         * @param bool $liked
         * @throws DatabaseException
         */
        public function registerRecord(int $user_id, string $post_id, bool $liked=True): void
        {
            $Query = QueryBuilder::insert_into("likes", [
                "id" => ($user_id . $post_id),
                "user_id" => $user_id,
                "post_id" => $post_id,
                "liked" => (int)$liked,
                "last_updated_timestamp" => time(),
                "created_timestamp" => time()
            ]);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);
            if($QueryResults == false)
            {
                throw new DatabaseException("There was an error while trying to create a like record",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Gets an existing record from the database
         *
         * @param int $user_id
         * @param string $post_id
         * @return LikeRecord
         * @throws DatabaseException
         * @throws LikeRecordNotFoundException
         */
        public function getRecord(int $user_id, string $post_id): LikeRecord
        {
            $Query = QueryBuilder::select("likes", [
                "id",
                "user_id",
                "post_id",
                "liked",
                "last_updated_timestamp",
                "created_timestamp"
            ], "id", ($user_id . $post_id), null, null, 1);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new LikeRecordNotFoundException();
                }

                return(LikeRecord::fromArray($Row));
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying retrieve the like record from the network",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Updates an existing like record
         *
         * @param LikeRecord $likeRecord
         * @throws DatabaseException
         */
        public function updateRecord(LikeRecord $likeRecord): void
        {
            $Query = QueryBuilder::update("likes", [
                "liked" => (int)$likeRecord->Liked,
                "last_updated_timestamp" => time()
            ], "id", ($likeRecord->UserID . $likeRecord->PostID));
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults == false)
            {
                throw new DatabaseException(
                    "There was an error while trying to update the like record",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }
    }
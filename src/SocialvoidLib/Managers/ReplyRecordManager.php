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
    use SocialvoidLib\Exceptions\Internal\QuoteRecordNotFoundException;
    use SocialvoidLib\Objects\ReplyRecord;
    use SocialvoidLib\SocialvoidLib;

    /**
     * Class ReplyRecordManager
     * @package SocialvoidLib\Managers
     */
    class ReplyRecordManager
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * ReplyRecordManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Creates a quote record if one doesn't already exist, or updates an existing one
         *
         * @param int $user_id
         * @param int $reply_post_id
         * @param int $post_id
         * @throws DatabaseException
         */
        public function replyRecord(int $user_id, int $post_id, int $reply_post_id)
        {
            try
            {
                $record = $this->getRecord($post_id, $reply_post_id);
            }
            catch(QuoteRecordNotFoundException $e)
            {
                $this->registerRecord($user_id, $post_id, $reply_post_id, true);
                return;
            }


            $record->Replied = true;
            $this->updateRecord($record);
        }

        /**
         * Creates a repost record if one doesn't already exist, or updates an existing one
         *
         * @param int $user_id
         * @param int $post_id
         * @param int $reply_post_id
         * @throws DatabaseException
         */
        public function unreplyRecord(int $user_id, int $post_id, int $reply_post_id)
        {
            try
            {
                $record = $this->getRecord($post_id, $reply_post_id);
            }
            catch(QuoteRecordNotFoundException $e)
            {
                $this->registerRecord($user_id, $post_id, $reply_post_id, false);
                return;
            }

            $record->Replied = false;
            $this->updateRecord($record);
        }

        /**
         * Registers a new quote record into the database
         *
         * @param int $user_id
         * @param int $post_id
         * @param int $reply_post_id
         * @param bool $replied
         * @throws DatabaseException
         */
        public function registerRecord(int $user_id, int $post_id, int $reply_post_id, bool $replied=True): void
        {
            $Query = QueryBuilder::insert_into("replies", [
                "id" => (double)((int)$post_id . (int)$reply_post_id),
                "user_id" => (int)$user_id,
                "post_id" => (int)$post_id,
                "reply_post_id" => (int)$reply_post_id,
                "replied" => (int)$replied,
                "last_updated_timestamp" => (int)time(),
                "created_timestamp" => (int)time()
            ]);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);
            if($QueryResults == false)
            {
                throw new DatabaseException("There was an error while trying to create a reply record",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Gets an existing record from the database
         *
         * @param int $post_id
         * @param int $reply_post_id
         * @return ReplyRecord
         * @throws DatabaseException
         * @throws QuoteRecordNotFoundException
         * @noinspection DuplicatedCode
         */
        public function getRecord(int $post_id, int $reply_post_id): ReplyRecord
        {
            $Query = QueryBuilder::select("replies", [
                "id",
                "user_id",
                "post_id",
                "reply_post_id",
                "replied",
                "last_updated_timestamp",
                "created_timestamp"
            ], "id", (double)((int)$post_id . (int)$reply_post_id), null, null, 1);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new QuoteRecordNotFoundException();
                }

                return(ReplyRecord::fromArray($Row));
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying retrieve the reply record from the network",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Updates an existing quote record
         *
         * @param ReplyRecord $replyRecord
         * @throws DatabaseException
         */
        public function updateRecord(ReplyRecord $replyRecord): void
        {
            $Query = QueryBuilder::update("replies", [
                "replied" => (int)$replyRecord->Replied,
                "last_updated_timestamp" => (int)time()
            ], "id", (double)((int)$replyRecord->PostID . (int)$replyRecord->ReplyPostID));
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults == false)
            {
                throw new DatabaseException(
                    "There was an error while trying to update the reply record",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }
    }
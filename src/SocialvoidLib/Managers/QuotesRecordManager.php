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
    use SocialvoidLib\Objects\QuoteRecord;
    use SocialvoidLib\SocialvoidLib;

    /**
     * Class QuotesRecordManager
     * @package SocialvoidLib\Managers
     */
    class QuotesRecordManager
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * QuotesRecordManager constructor.
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
         * @param int $post_id
         * @throws DatabaseException
         */
        public function quoteRecord(int $user_id, int $post_id)
        {
            try
            {
                $record = $this->getRecord($user_id, $post_id);
            }
            catch(QuoteRecordNotFoundException $e)
            {
                $this->registerRecord($user_id, $post_id, true);
                return;
            }


            $record->Quoted = true;
            $this->updateRecord($record);
        }

        /**
         * Creates a repost record if one doesn't already exist, or updates an existing one
         *
         * @param int $user_id
         * @param int $post_id
         * @throws DatabaseException
         */
        public function unquoteRecord(int $user_id, int $post_id)
        {
            try
            {
                $record = $this->getRecord($user_id, $post_id);
            }
            catch(QuoteRecordNotFoundException $e)
            {
                $this->registerRecord($user_id, $post_id, false);
                return;
            }

            $record->Quoted = false;
            $this->updateRecord($record);
        }

        /**
         * Registers a new quote record into the database
         *
         * @param int $user_id
         * @param int $post_id
         * @param int $original_post_id
         * @param bool $quoted
         * @throws DatabaseException
         */
        public function registerRecord(int $user_id, int $post_id, int $original_post_id,bool $quoted=True): void
        {
            $Query = QueryBuilder::insert_into("quotes", [
                "id" => (double)((int)$user_id . (int)$post_id),
                "user_id" => (int)$user_id,
                "post_id" => (int)$post_id,
                "original_post_id" => (int)$original_post_id,
                "quoted" => (int)$quoted,
                "last_updated_timestamp" => (int)time(),
                "created_timestamp" => (int)time()
            ]);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);
            if($QueryResults == false)
            {
                throw new DatabaseException("There was an error while trying to create a quote record",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Gets an existing record from the database
         *
         * @param int $user_id
         * @param int $post_id
         * @return QuoteRecord
         * @throws DatabaseException
         * @throws QuoteRecordNotFoundException
         * @noinspection DuplicatedCode
         */
        public function getRecord(int $user_id, int $post_id): QuoteRecord
        {
            $Query = QueryBuilder::select("quotes", [
                "id",
                "user_id",
                "post_id",
                "original_post_id",
                "quoted",
                "last_updated_timestamp",
                "created_timestamp"
            ], "id", (double)((int)$user_id . (int)$post_id), null, null, 1);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new QuoteRecordNotFoundException();
                }

                return(QuoteRecord::fromArray($Row));
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying retrieve the quote record from the network",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Updates an existing quote record
         *
         * @param QuoteRecord $QuoteRecord
         * @throws DatabaseException
         */
        public function updateRecord(QuoteRecord $QuoteRecord): void
        {
            $Query = QueryBuilder::update("quotes", [
                "quoted" => (int)$QuoteRecord->Quoted,
                "last_updated_timestamp" => (int)time()
            ], "id", (double)((int)$QuoteRecord->UserID . (int)$QuoteRecord->PostID));
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults == false)
            {
                throw new DatabaseException(
                    "There was an error while trying to update the quote record",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }
    }
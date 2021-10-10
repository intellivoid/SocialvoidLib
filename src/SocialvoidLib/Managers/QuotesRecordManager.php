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
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSlaveHashException;
    use SocialvoidLib\Exceptions\Internal\QuoteRecordNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PostNotFoundException;
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
         * @param string $post_id
         * @param string $original_post_id
         * @throws DatabaseException
         * @throws PostNotFoundException
         * @throws QuoteRecordNotFoundException
         */
        public function quoteRecord(int $user_id, string $post_id, string $original_post_id)
        {
            try
            {
                $record = $this->getRecord($post_id, $original_post_id);
            }
            catch(QuoteRecordNotFoundException $e)
            {
                $this->registerRecord($user_id, $post_id, $original_post_id, true);
                return;
            }
            
            $record->Quoted = true;
            $this->updateRecord($record);
        }

        /**
         * Creates a repost record if one doesn't already exist, or updates an existing one
         *
         * @param int $user_id
         * @param string $post_id
         * @param string $original_post_id
         * @throws DatabaseException
         * @throws PostNotFoundException
         * @throws QuoteRecordNotFoundException
         */
        public function unquoteRecord(int $user_id, string $post_id, string $original_post_id)
        {
            try
            {
                $record = $this->getRecord($post_id, $original_post_id);
            }
            catch(QuoteRecordNotFoundException $e)
            {
                $this->registerRecord($user_id, $post_id, $original_post_id, false);
                return;
            }

            $record->Quoted = false;
            $this->updateRecord($record);
        }

        /**
         * Returns an array of Post IDs that quoted the requested post
         *
         * @param string $post_id
         * @param int $offset
         * @param int $limit
         * @return array
         * @throws DatabaseException
         * @throws PostNotFoundException
         * @noinspection DuplicatedCode
         */
        public function getQuotes(string $post_id, int $offset=0, int $limit=100): array
        {
            try
            {
                $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer(Utilities::getSlaveHash($post_id));
            }
            catch (InvalidSlaveHashException $e)
            {
                throw new PostNotFoundException();
            }

            $post_id = $this->socialvoidLib->getDatabase()->real_escape_string(Utilities::removeSlaveHash($post_id));
            $Query = "SELECT post_id FROM `posts_quotes` WHERE original_post_id='$post_id' AND quoted=1 LIMIT $offset, $limit";
            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            // Execute and process the query
            if($QueryResults == false)
            {
                throw new DatabaseException('There was an error while trying to get the quotes from this post',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
            else
            {
                $ResultsArray = [];

                while($Row = $QueryResults->fetch_assoc())
                {
                    $ResultsArray[] = $Row['post_id'];
                }
            }

            return $ResultsArray;
        }

        /**
         * Returns the number of quotes that this post has had
         *
         * @param string $post_id
         * @return int
         * @throws DatabaseException
         * @throws PostNotFoundException
         */
        public function getQuotesCount(string $post_id): int
        {
            try
            {
                $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer(Utilities::getSlaveHash($post_id));
            }
            catch (InvalidSlaveHashException $e)
            {
                throw new PostNotFoundException();
            }

            $post_id = $this->socialvoidLib->getDatabase()->real_escape_string(Utilities::removeSlaveHash($post_id));
            $Query = "SELECT COUNT(*) AS total FROM `posts_quotes` WHERE original_post_id='$post_id' AND quoted=1";
            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            // Execute and process the query
            if($QueryResults == false)
            {
                throw new DatabaseException('There was an error while trying to get the quotes from this post',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }

            return (int)$QueryResults->fetch_assoc()['total'];
        }

        /**
         * Registers a new quote record into the database
         *
         * @param int $user_id
         * @param string $post_id
         * @param string $original_post_id
         * @param bool $quoted
         * @throws DatabaseException
         * @throws PostNotFoundException
         */
        public function registerRecord(int $user_id, string $post_id, string $original_post_id, bool $quoted=True): void
        {
            $Query = QueryBuilder::insert_into('posts_quotes', [
                'id' => ($post_id . Utilities::removeSlaveHash($original_post_id)),
                'user_id' => $user_id,
                'post_id' => $post_id,
                'original_post_id' => Utilities::removeSlaveHash($original_post_id),
                'quoted' => (int)$quoted,
                'last_updated_timestamp' => time(),
                'created_timestamp' => time()
            ]);

            try
            {
                $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer(Utilities::getSlaveHash($original_post_id));
            }
            catch (InvalidSlaveHashException $e)
            {
                throw new PostNotFoundException();
            }

            $QueryResults = $SelectedSlave->getConnection()->query($Query);
            if($QueryResults == false)
            {
                throw new DatabaseException('There was an error while trying to create a quote record',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
        }

        /**
         * Gets an existing record from the database
         *
         * @param string $post_id
         * @param string $original_post_id
         * @return QuoteRecord
         * @throws DatabaseException
         * @throws PostNotFoundException
         * @throws QuoteRecordNotFoundException
         */
        public function getRecord(string $post_id, string $original_post_id): QuoteRecord
        {
            $Query = QueryBuilder::select('posts_quotes', [
                'id',
                'user_id',
                'post_id',
                'original_post_id',
                'quoted',
                'last_updated_timestamp',
                'created_timestamp'
            ], 'id', ($post_id . Utilities::removeSlaveHash($original_post_id)), null, null, 1);

            try
            {
                $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer(Utilities::getSlaveHash($original_post_id));
            }
            catch (InvalidSlaveHashException $e)
            {
                throw new PostNotFoundException();
            }

            $QueryResults = $SelectedSlave->getConnection()->query($Query);

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
                    'There was an error while trying retrieve the quote record from the network',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
        }

        /**
         * Updates an existing quote record
         *
         * @param QuoteRecord $QuoteRecord
         * @throws DatabaseException
         * @throws QuoteRecordNotFoundException
         */
        public function updateRecord(QuoteRecord $QuoteRecord): void
        {
            $Query = QueryBuilder::update('posts_quotes', [
                'quoted' => (int)$QuoteRecord->Quoted,
                'last_updated_timestamp' => time()
            ], 'id', ($QuoteRecord->PostID . Utilities::removeSlaveHash($QuoteRecord->OriginalPostID)));

            try
            {
                $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer(Utilities::getSlaveHash($QuoteRecord->PostID));
            }
            catch (InvalidSlaveHashException $e)
            {
                throw new QuoteRecordNotFoundException();
            }

            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            if($QueryResults == false)
            {
                throw new DatabaseException(
                    'There was an error while trying to update the quote record',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
        }
    }
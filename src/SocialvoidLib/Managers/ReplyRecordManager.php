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
    use SocialvoidLib\Exceptions\Internal\ReplyRecordNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PostNotFoundException;
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
         * @param string $post_id
         * @param string $reply_post_id
         * @throws DatabaseException
         * @throws PostNotFoundException
         * @throws ReplyRecordNotFoundException
         */
        public function replyRecord(int $user_id, string $post_id, string $reply_post_id)
        {
            try
            {
                $record = $this->getRecord($post_id, $reply_post_id);
            }
            catch(ReplyRecordNotFoundException $e)
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
         * @param string $post_id
         * @param string $reply_post_id
         * @throws DatabaseException
         * @throws PostNotFoundException
         * @throws ReplyRecordNotFoundException
         */
        public function unreplyRecord(int $user_id, string $post_id, string $reply_post_id)
        {
            try
            {
                $record = $this->getRecord($post_id, $reply_post_id);
            }
            catch(ReplyRecordNotFoundException $e)
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
         * @param string $post_id
         * @param string $reply_post_id
         * @param bool $replied
         * @throws DatabaseException
         * @throws PostNotFoundException
         */
        public function registerRecord(int $user_id, string $post_id, string $reply_post_id, bool $replied=True): void
        {
            $Query = QueryBuilder::insert_into('posts_replies', [
                'id' => (Utilities::removeSlaveHash($post_id) . $reply_post_id),
                'user_id' => $user_id,
                'post_id' => Utilities::removeSLaveHash($post_id),
                'reply_post_id' => $reply_post_id,
                'replied' => (int)$replied,
                'last_updated_timestamp' => time(),
                'created_timestamp' => time()
            ]);

            try
            {
                $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer(Utilities::getSlaveHash($post_id));
            }
            catch (InvalidSlaveHashException $e)
            {
                throw new PostNotFoundException();
            }

            $QueryResults = $SelectedSlave->getConnection()->query($Query);
            if($QueryResults == false)
            {
                throw new DatabaseException('There was an error while trying to create a reply record',
                    $Query,$SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
        }

        /**
         * Gets an existing record from the database
         *
         * @param string $post_id
         * @param string $reply_post_id
         * @return ReplyRecord
         * @throws DatabaseException
         * @throws PostNotFoundException
         * @throws ReplyRecordNotFoundException
         */
        public function getRecord(string $post_id, string $reply_post_id): ReplyRecord
        {
            $Query = QueryBuilder::select('posts_replies', [
                'id',
                'user_id',
                'post_id',
                'reply_post_id',
                'replied',
                'last_updated_timestamp',
                'created_timestamp'
            ], 'id', (Utilities::removeSlaveHash($post_id) . $reply_post_id), null, null, 1);

            try
            {
                $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer(Utilities::getSlaveHash($post_id));
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
                    throw new ReplyRecordNotFoundException();
                }

                return(ReplyRecord::fromArray($Row));
            }
            else
            {
                throw new DatabaseException(
                    'There was an error while trying retrieve the reply record from the network',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
        }

        /**
         * Updates an existing quote record
         *
         * @param ReplyRecord $replyRecord
         * @throws DatabaseException
         * @throws ReplyRecordNotFoundException
         */
        public function updateRecord(ReplyRecord $replyRecord): void
        {
            $Query = QueryBuilder::update('posts_replies', [
                'replied' => (int)$replyRecord->Replied,
                'last_updated_timestamp' => time()
            ], 'id', (Utilities::removeSlaveHash($replyRecord->PostID) . $replyRecord->ReplyPostID));

            try
            {
                $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer(Utilities::getSlaveHash($replyRecord->PostID));
            }
            catch (InvalidSlaveHashException $e)
            {
                throw new ReplyRecordNotFoundException();
            }

            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            if($QueryResults == false)
            {
                throw new DatabaseException(
                    'There was an error while trying to update the reply record',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
        }
    }
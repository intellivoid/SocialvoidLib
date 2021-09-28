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
    use SocialvoidLib\Exceptions\Internal\RepostRecordNotFoundException;
    use SocialvoidLib\Objects\RepostRecord;
    use SocialvoidLib\Objects\User;
    use SocialvoidLib\SocialvoidLib;

    /**
     * Class RepostsRecordManager
     * @package SocialvoidLib\Managers
     */
    class RepostsRecordManager
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;


        /**
         * RepostsRecordManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Creates a repost record if one doesn't already exist, or updates an existing one
         *
         * @param int $user_id
         * @param string $post_id
         * @param string $original_post_id
         * @throws DatabaseException
         * @throws InvalidSlaveHashException
         */
        public function repostRecord(User $user, string $post_id, string $original_post_id)
        {
            try
            {
                $record = $this->getRepostedRecord($user, $original_post_id);
            }
            catch(RepostRecordNotFoundException $e)
            {
                $this->registerRecord($user->ID, $post_id, $original_post_id, true);
                return;
            }

            $record->Reposted = true;
            $record->PostID = $post_id;
            $this->updateRecord($record);
        }

        /**
         * Creates a repost record if one doesn't already exist, or updates an existing one
         *
         * @param int $user_id
         * @param string $post_id
         * @param string $original_post_id
         * @throws DatabaseException
         * @throws InvalidSlaveHashException
         */
        public function unrepostRecord(int $user_id, string $post_id, string $original_post_id)
        {
            try
            {
                $record = $this->getRecord($user_id, $original_post_id);
            }
            catch(RepostRecordNotFoundException $e)
            {
                $this->registerRecord($user_id, $post_id, $original_post_id);
                return;
            }

            $record->Reposted = false;
            $record->PostID = null;
            $this->updateRecord($record);
        }

        /**
         * Registers a new repost record into the database
         *
         * @param int $user_id
         * @param string|null $post_id
         * @param string $original_post_id
         * @param bool $reposted
         * @throws DatabaseException
         * @throws InvalidSlaveHashException
         */
        public function registerRecord(int $user_id, string $post_id, string $original_post_id, bool $reposted=True): void
        {
            $Query = QueryBuilder::insert_into('posts_reposts', [
                'id' => (Utilities::removeSlaveHash($post_id) . $original_post_id),
                'user_id' => $user_id,
                'post_id' => Utilities::removeSlaveHash($post_id),
                'original_post_id' => $original_post_id,
                'reposted' => (int)$reposted,
                'last_updated_timestamp' => time(),
                'created_timestamp' => time()
            ]);

            $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer(Utilities::getSlaveHash($post_id));
            $QueryResults = $SelectedSlave->getConnection()->query($Query);
            if($QueryResults == false)
            {
                throw new DatabaseException('There was an error while trying to create a repost record',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
        }

        /**
         * Gets an existing record from the database
         *
         * @param int $user_id
         * @param string $post_id
         * @return RepostRecord
         * @throws DatabaseException
         * @throws InvalidSlaveHashException
         * @throws RepostRecordNotFoundException
         */
        public function getRecord(int $user_id, string $post_id): RepostRecord
        {
            $Query = QueryBuilder::select('posts_reposts', [
                'id',
                'user_id',
                'post_id',
                'original_post_id',
                'reposted',
                'last_updated_timestamp',
                'created_timestamp'
            ], 'id', (Utilities::removeSlaveHash($post_id) . $user_id), null, null, 1);

            $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer(Utilities::getSlaveHash($post_id));
            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new RepostRecordNotFoundException();
                }

                return(RepostRecord::fromArray($Row));
            }
            else
            {
                throw new DatabaseException(
                    'There was an error while trying retrieve the repost record from the network',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
        }

        /**
         * Returns a reposted record
         *
         * @param User $user
         * @param string $original_post_id
         * @return RepostRecord
         * @throws DatabaseException
         * @throws InvalidSlaveHashException
         * @throws RepostRecordNotFoundException
         */
        public function getRepostedRecord(User $user, string $original_post_id): RepostRecord
        {
            /** @noinspection PhpCastIsUnnecessaryInspection */
            $user_id = (int)$user->ID;
            $x_original_post_id = $this->socialvoidLib->getDatabase()->real_escape_string($original_post_id);
            $Query = "SELECT id, user_id, post_id, original_post_id, reposted, last_updated_timestamp, created_timestamp FROM posts_reposts WHERE user_id=$user_id AND original_post_id='$x_original_post_id' LIMIT 1;";
            $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer($user->SlaveServer);
            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new RepostRecordNotFoundException();
                }

                return(RepostRecord::fromArray($Row));
            }
            else
            {
                throw new DatabaseException(
                    'There was an error while trying retrieve the repost record from the network',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
        }

        /**
         * Updates an existing repost record
         *
         * @param RepostRecord $repostRecord
         * @throws DatabaseException
         * @throws InvalidSlaveHashException
         */
        public function updateRecord(RepostRecord $repostRecord): void
        {
            $Query = QueryBuilder::update('posts_reposts', [
                'reposted' => (int)$repostRecord->Reposted,
                'last_updated_timestamp' => time()
            ], 'id', (Utilities::removeSlaveHash($repostRecord->PostID) . $repostRecord->UserID));

            $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer(Utilities::getSlaveHash($repostRecord->PostID));
            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            if($QueryResults == false)
            {
                throw new DatabaseException(
                    'There was an error while trying to update the repost record',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
        }
    }
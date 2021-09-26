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

    // TODO: Add method to get all likes from one post

    namespace SocialvoidLib\Managers;

    use msqg\QueryBuilder;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSlaveHashException;
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
         * @param string $slave_server
         * @param int $user_id
         * @param string $post_id
         * @throws DatabaseException
         * @throws InvalidSlaveHashException
         */
        public function likeRecord(string $slave_server, int $user_id, string $post_id)
        {
            try
            {
                $record = $this->getRecord($slave_server, $user_id, $post_id);
            }
            catch(LikeRecordNotFoundException $e)
            {
                $this->registerRecord($slave_server, $user_id, $post_id, true);
                return;
            }

            $record->Liked = true;
            $this->updateRecord($record);
        }

        /**
         * Creates a like record if one doesn't already exist, or updates an existing one
         *
         * @param string $salve_server
         * @param int $user_id
         * @param string $post_id
         * @throws DatabaseException
         * @throws InvalidSlaveHashException
         */
        public function unlikeRecord(string $salve_server, int $user_id, string $post_id)
        {
            try
            {
                $record = $this->getRecord($salve_server, $user_id, $post_id);
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
         * @param string $slave_server
         * @param int $user_id
         * @param string $post_id
         * @param bool $liked
         * @throws DatabaseException
         * @throws InvalidSlaveHashException
         */
        public function registerRecord(string $slave_server, int $user_id, string $post_id, bool $liked=True): void
        {
            $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer($slave_server);

            $Query = QueryBuilder::insert_into('likes', [
                'id' => ($user_id . $post_id),
                'user_id' => $user_id,
                'post_id' => $post_id,
                'liked' => (int)$liked,
                'last_updated_timestamp' => time(),
                'created_timestamp' => time()
            ]);

            $QueryResults = $SelectedSlave->getConnection()->query($Query);
            if($QueryResults == false)
            {
                throw new DatabaseException('There was an error while trying to create a like record',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
        }

        /**
         * Returns an array of user IDs that liked this post
         *
         * @param string $slave_server
         * @param string $post_id
         * @param int $offset
         * @param int $limit
         * @return array
         * @throws DatabaseException
         * @throws InvalidSlaveHashException
         */
        public function getLikes(string $slave_server, string $post_id, int $offset=0, int $limit=100): array
        {
            $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer($slave_server);
            $post_id = $this->socialvoidLib->getDatabase()->real_escape_string($post_id);
            $Query = "SELECT user_id, FROM `likes` WHERE post_id='$post_id' AND liked=1 LIMIT $offset, $limit";
            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            // Execute and process the query
            if($QueryResults == false)
            {
                throw new DatabaseException('There was an error while trying to get the likes from this post',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
            else
            {
                $ResultsArray = [];

                while($Row = $QueryResults->fetch_assoc())
                {
                    $ResultsArray[] = $Row['user_id'];
                }
            }

            return $ResultsArray;
        }

        /**
         * Gets an existing record from the database
         *
         * @param string $slave_server
         * @param int $user_id
         * @param string $post_id
         * @return LikeRecord
         * @throws DatabaseException
         * @throws InvalidSlaveHashException
         * @throws LikeRecordNotFoundException
         */
        public function getRecord(string $slave_server, int $user_id, string $post_id): LikeRecord
        {
            $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer($slave_server);

            $Query = QueryBuilder::select('likes', [
                'id',
                'user_id',
                'post_id',
                'liked',
                'last_updated_timestamp',
                'created_timestamp'
            ], 'id', ($user_id . $post_id), null, null, 1);
            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new LikeRecordNotFoundException();
                }

                $ReturnObject = LikeRecord::fromArray($Row);
                $ReturnObject->ID = $SelectedSlave->MysqlServerPointer->HashPointer . '-' . $ReturnObject->ID;
                return $ReturnObject;
            }
            else
            {
                throw new DatabaseException(
                    'There was an error while trying retrieve the like record from the network',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
        }

        /**
         * Updates an existing like record
         *
         * @param LikeRecord $likeRecord
         * @throws DatabaseException
         * @throws InvalidSlaveHashException
         */
        public function updateRecord(LikeRecord $likeRecord): void
        {
            $Query = QueryBuilder::update('likes', [
                'liked' => (int)$likeRecord->Liked,
                'last_updated_timestamp' => time()
            ], 'id', ($likeRecord->UserID . $likeRecord->PostID));

            $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer(Utilities::getSlaveHash($likeRecord->ID));
            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            if($QueryResults == false)
            {
                throw new DatabaseException(
                    'There was an error while trying to update the like record',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }
        }
    }
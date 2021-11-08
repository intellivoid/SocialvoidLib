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
    use SocialvoidLib\Exceptions\Standard\Network\PostNotFoundException;
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
         * @return bool
         * @throws DatabaseException
         * @throws LikeRecordNotFoundException
         * @throws PostNotFoundException
         */
        public function likeRecord(int $user_id, string $post_id): bool
        {
            try
            {
                $record = $this->getRecord($user_id, $post_id);
            }
            catch(LikeRecordNotFoundException $e)
            {
                $this->registerRecord($user_id, $post_id, true);
                return true;
            }

            if($record->Liked == true)
                return false;
            $record->Liked = true;
            $this->updateRecord($record);
            return true;
        }

        /**
         * Creates a like record if one doesn't already exist, or updates an existing one
         *
         * @param int $user_id
         * @param string $post_id
         * @return bool
         * @throws DatabaseException
         * @throws LikeRecordNotFoundException
         * @throws PostNotFoundException
         */
        public function unlikeRecord(int $user_id, string $post_id): bool
        {
            try
            {
                $record = $this->getRecord($user_id, $post_id);
            }
            catch(LikeRecordNotFoundException $e)
            {
                $this->registerRecord($user_id, $post_id, false);
                return true;
            }

            if($record->Liked == false)
                return false;
            $record->Liked = false;
            $this->updateRecord($record);
            return true;
        }

        /**
         * Registers a new like record into the database
         *
         * @param int $user_id
         * @param string $post_id
         * @param bool $liked
         * @throws DatabaseException
         * @throws PostNotFoundException
         */
        public function registerRecord(int $user_id, string $post_id, bool $liked=True): void
        {
            try
            {
                $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer(Utilities::getSlaveHash($post_id));
            }
            catch (InvalidSlaveHashException $e)
            {
                throw new PostNotFoundException();
            }

            $Query = QueryBuilder::insert_into('posts_likes', [
                'id' => ($user_id . Utilities::removeSlaveHash($post_id)),
                'user_id' => $user_id,
                'post_id' => Utilities::removeSlaveHash($post_id),
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
         * @param string $post_id
         * @param int $offset
         * @param int $limit
         * @return array
         * @throws DatabaseException
         * @throws PostNotFoundException
         * @noinspection SqlResolve
         */
        public function getLikes(string $post_id, int $offset=0, int $limit=100): array
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
            $Query = "SELECT user_id FROM `posts_likes` WHERE post_id='$post_id' AND liked=1 LIMIT $offset, $limit";
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
         * Returns the amount of likes for a post
         *
         * @param string $post_id
         * @return int
         * @throws DatabaseException
         * @throws PostNotFoundException
         * @noinspection DuplicatedCode
         */
        public function getLikesCount(string $post_id): int
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
            $Query = "SELECT COUNT(*) AS total FROM `posts_likes` WHERE post_id='$post_id' AND liked=1";
            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            // Execute and process the query
            if($QueryResults == false)
            {
                throw new DatabaseException('There was an error while trying to get the likes from this post',
                    $Query, $SelectedSlave->getConnection()->error, $SelectedSlave->getConnection()
                );
            }

            return (int)$QueryResults->fetch_assoc()['total'];
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
            try
            {
                $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer(Utilities::getSlaveHash($post_id));
            }
            catch (InvalidSlaveHashException $e)
            {
                throw new LikeRecordNotFoundException();
            }

            $Query = QueryBuilder::select('posts_likes', [
                'id',
                'user_id',
                'post_id',
                'liked',
                'last_updated_timestamp',
                'created_timestamp'
            ], 'id', ($user_id . Utilities::removeSlaveHash($post_id)), null, null, 1);
            $QueryResults = $SelectedSlave->getConnection()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new LikeRecordNotFoundException();
                }

                $ReturnObject = LikeRecord::fromArray($Row);
                $ReturnObject->PostID = $SelectedSlave->MysqlServerPointer->HashPointer . '-' . $ReturnObject->PostID;
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
         * @throws LikeRecordNotFoundException
         */
        public function updateRecord(LikeRecord $likeRecord): void
        {
            $Query = QueryBuilder::update('posts_likes', [
                'liked' => (int)$likeRecord->Liked,
                'last_updated_timestamp' => time()
            ], 'id', ($likeRecord->UserID . Utilities::removeSlaveHash($likeRecord->PostID)));

            try
            {
                $SelectedSlave = $this->socialvoidLib->getSlaveManager()->getMySqlServer(Utilities::getSlaveHash($likeRecord->PostID));
            }
            catch (InvalidSlaveHashException $e)
            {
                throw new LikeRecordNotFoundException();
            }

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
<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    /** @noinspection PhpUnused */

    namespace SocialvoidLib\Managers;

    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\StatusStates\RelationState;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Objects\Peer;
    use SocialvoidLib\SocialvoidLib;

    /**
     * Class FollowerStateManager
     * @package SocialvoidLib\Managers
     */
    class RelationStateManager
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * FollowerStateManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Registers a relation state into the database, drops it if none.
         *
         * @param Peer $user
         * @param Peer $target_user
         * @param int $state
         * @throws DatabaseException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function registerState(Peer $user, Peer $target_user, int $state)
        {
            // Drop the state if it's none
            if($state == RelationState::None)
            {
                $this->dropState($user, $target_user);
                return;
            }

            // Update the state if it already exists
            if($this->getState($user, $target_user) !== RelationState::None)
            {
                $this->updateState($user, $target_user, $state);
                return;
            }

            $Query = QueryBuilder::insert_into('peer_relations', [
                'user_id' => (int)$user->ID,
                'target_user_id' => (int)$target_user->ID,
                'state' => (int)$state,
                'last_updated_timestamp' => (int)time(),
                'created_timestamp' => (int)time()
            ]);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);
            if($QueryResults == false)
            {
                throw new DatabaseException('There was an error while trying to register the following state',
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Drops an existing relation state from the database
         *
         * @param Peer $user
         * @param Peer $target_user
         * @throws DatabaseException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function dropState(Peer $user, Peer $target_user)
        {
            $user_id = (int)$user->ID;
            $target_user_id = (int)$target_user->ID;
            $Query = "DELETE FROM `peer_relations` WHERE user_id=$user_id AND target_user_id=$target_user_id LIMIT 1";

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);
            if($QueryResults == false)
            {
                throw new DatabaseException('There was an error while trying to drop the relation state',
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Gets an existing following state from the database
         *
         * @param Peer $user
         * @param Peer $target_user
         * @return int|RelationState
         * @throws DatabaseException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function getState(Peer $user, Peer $target_user): int
        {
            $user_id = (int)$user->ID;
            $target_user_id = (int)$target_user->ID;
            $Query = "SELECT state FROM `peer_relations` WHERE user_id=$user_id AND target_user_id=$target_user_id LIMIT 1";
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    return RelationState::None;
                }
                else
                {
                    return (int)$Row['state'];
                }
            }
            else
            {
                throw new DatabaseException(
                    'There was an error while trying retrieve the following state',
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Updates an existing following state
         *
         * @param Peer $user
         * @param Peer $target_user
         * @param int $state
         * @return void
         * @throws DatabaseException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function updateState(Peer $user, Peer $target_user, int $state)
        {
            $state = (int)$state;
            $user_id = (int)$user->ID;
            $target_user_id = (int)$target_user->ID;
            $Query = "UPDATE `peer_relations` SET state=$state WHERE user_id=$user_id AND target_user_id=$target_user_id LIMIT 1";
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults == false)
            {
                throw new DatabaseException(
                    'There was an error while trying to update the relation state',
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Returns the number of followers that this peer has
         *
         * @param Peer $user
         * @return int
         * @throws DatabaseException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function getFollowersCount(Peer $user): int
        {
            $user_id = (int)$user->ID;
            $state = (int)RelationState::Following;
            $Query = "SELECT COUNT(*) AS total FROM `peer_relations` WHERE target_user_id='$user_id' AND state=$state";

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            // Execute and process the query
            if($QueryResults == false)
            {
                throw new DatabaseException('There was an error while trying to get the followers count from this user',
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }

            return (int)$QueryResults->fetch_assoc()['total'];
        }

        /**
         * Returns an array of User IDs that currently follows the requested peer
         *
         * @param Peer $user
         * @param int $limit
         * @param int $offset
         * @return array|int
         * @throws DatabaseException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function getFollowers(Peer $user, int $limit=100, int $offset=0): array
        {
            $user_id = (int)$user->ID;
            $state = (int)RelationState::Following;
            $limit = (int)$limit;
            $offset = (int)$offset;

            $Query = "SELECT user_id FROM `peer_relations` WHERE target_user_id='$user_id' AND state=$state ORDER BY created_timestamp DESC LIMIT $offset, $limit";

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);
            // Execute and process the query
            if($QueryResults == false)
            {
                throw new DatabaseException('There was an error while trying to get the followers from the requested peer',
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
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
         * Returns an array of user IDs that this peer follows
         *
         * @param Peer $user
         * @param int $limit
         * @param int $offset
         * @return array
         * @throws DatabaseException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function getFollowing(Peer $user, int $limit=100, int $offset=0): array
        {
            $user_id = (int)$user->ID;
            $state = (int)RelationState::Following;
            $limit = (int)$limit;
            $offset = (int)$offset;

            $Query = "SELECT target_user_id FROM `peer_relations` WHERE user_id='$user_id' AND state=$state ORDER BY created_timestamp DESC LIMIT $offset, $limit";
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            // Execute and process the query
            if($QueryResults == false)
            {
                throw new DatabaseException('There was an error while trying to get the following data from the requested peer',
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
            else
            {
                $ResultsArray = [];

                while($Row = $QueryResults->fetch_assoc())
                {
                    $ResultsArray[] = $Row['target_user_id'];
                }
            }

            return $ResultsArray;
        }

        /**
         * Returns the number of peers that this peer is following
         *
         * @param Peer $user
         * @return int
         * @throws DatabaseException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function getFollowingCount(Peer $user): int
        {
            $user_id = (int)$user->ID;
            $state = (int)RelationState::Following;
            $Query = "SELECT COUNT(*) AS total FROM `peer_relations` WHERE user_id='$user_id' AND state=$state";

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            // Execute and process the query
            if($QueryResults == false)
            {
                throw new DatabaseException('There was an error while trying to get the followers count from this user',
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }

            return (int)$QueryResults->fetch_assoc()['total'];
        }

    }
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
    use SocialvoidLib\Abstracts\Types\Standard\RelationshipType;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Objects\User;
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
         * @param User $user
         * @param User $target_user
         * @param int $state
         * @throws DatabaseException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function registerState(User $user, User $target_user, int $state)
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
         * @param User $user
         * @param User $target_user
         * @throws DatabaseException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function dropState(User $user, User $target_user)
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
         * @param User $user
         * @param User $target_user
         * @return int|RelationState
         * @throws DatabaseException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function getState(User $user, User $target_user): int
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
         * @param User $user
         * @param User $target_user
         * @param int $state
         * @return void
         * @throws DatabaseException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function updateState(User $user, User $target_user, int $state)
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
         * @param User $user
         * @return int
         * @throws DatabaseException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function getFollowersCount(User $user): int
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
         * Returns the number of peers that this peer is following
         *
         * @param User $user
         * @return int
         * @throws DatabaseException
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function getFollowingCount(User $user): int
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
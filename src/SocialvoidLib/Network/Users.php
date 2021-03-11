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


    namespace SocialvoidLib\Network;

    use Exception;
    use SocialvoidLib\Abstracts\SearchMethods\FollowerStateSearchMethod;
    use SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod;
    use SocialvoidLib\Abstracts\StatusStates\FollowerState;
    use SocialvoidLib\Classes\Standard\BaseIdentification;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Internal\FollowerDataNotFound;
    use SocialvoidLib\Exceptions\Internal\FollowerStateNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPeerInputException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\FollowerData;
    use SocialvoidLib\Objects\User;

    /**
     * Class Users
     * @package SocialvoidLib\Network
     */
    class Users
    {
        /**
         * @var NetworkSession
         */
        private NetworkSession $networkSession;

        /**
         * Users constructor.
         * @param NetworkSession $networkSession
         */
        public function __construct(NetworkSession $networkSession)
        {
            $this->networkSession = $networkSession;
        }

        /**
         * Resolves a peer ID, Username or Public ID.
         *
         * @param $peer
         * @param bool $cache_session
         * @return User
         * @throws InvalidPeerInputException
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws PeerNotFoundException
         */
        public function resolvePeer($peer, bool $cache_session=True): User
        {
            // Probably an ID
            if(ctype_digit($peer) || is_int($peer))
            {
                // Self-Resolved, no need to ask the database.
                if($peer == $this->networkSession->getAuthenticatedUser()->ID)
                    return $this->networkSession->getAuthenticatedUser();

                // Ask the database
                $peer_result = $this->networkSession->getSocialvoidLib()->getUserManager()->getUser(
                    UserSearchMethod::ById, $peer
                );
            }
            // It's a username
            elseif(substr($peer, 0, 1) == "@")
            {
                // Self-Resolved, no need to ask the database.
                if(strtolower(substr($peer, 1)) == $this->networkSession->getAuthenticatedUser()->UsernameSafe)
                    return $this->networkSession->getAuthenticatedUser();

                // Ask the database
                $peer_result = $this->networkSession->getSocialvoidLib()->getUserManager()->getUser(
                    UserSearchMethod::ByUsername, substr($peer, 1)
                );
            }
            // It's a public ID
            elseif(strlen($peer) == 64)
            {
                // Self-Resolved, no need to ask the database.
                if($peer == $this->networkSession->getAuthenticatedUser()->PublicID)
                    return $this->networkSession->getAuthenticatedUser();

                // Ask the database
                $peer_result = $this->networkSession->getSocialvoidLib()->getUserManager()->getUser(
                    UserSearchMethod::ByPublicId, $peer
                );
            }
            else
            {
                throw new InvalidPeerInputException("The given peer input is invalid", $peer);
            }

            // Cache the results partially to the session cache
            if($cache_session)
            {
                $this->networkSession->active_session->SessionCache->cachePeer($peer_result);
                $this->networkSession->updateActiveSession();
            }

            return $peer_result;
        }

        /**
         * Follows another peer on the network
         *
         * @param $peer
         * @return string
         * @throws DatabaseException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws PeerNotFoundException
         * @throws FollowerDataNotFound
         */
        public function followPeer($peer): string
        {
            // TODO: Update the timeline upon a follow event
            // Resolve the Peer ID
            $peer_id = null;
            $cache_peer = $this->networkSession->getActiveSession()->getSessionCache()->getCachedPeer($peer);

            if($cache_peer == null)
            {
                $peer_id = $this->resolvePeer($peer)->ID;
            }
            else
            {
                $peer_id = $cache_peer->PeerID;
            }

            $PublicID = BaseIdentification::FollowingStateID($this->networkSession->getAuthenticatedUser()->ID, $peer_id);

            try
            {
                $FollowerState = $this->networkSession->getSocialvoidLib()->getFollowerStateManager()->getFollowingState(
                    FollowerStateSearchMethod::ByPublicId, $PublicID
                );

                return $FollowerState->State;
            }
            catch(FollowerStateNotFoundException $e)
            {
                unset($e);
            }

            $TargetPeer = $this->resolvePeer($peer_id);

            $FollowerState = $this->networkSession->getSocialvoidLib()->getFollowerStateManager()->registerFollowingState(
                $this->networkSession->getAuthenticatedUser()->ID, $TargetPeer
            );

            if($FollowerState == FollowerState::Following)
            {
                // This user is following x
                $SelfFollowerData = $this->networkSession->getSocialvoidLib()->getFollowerDataManager()->resolveRecord(
                    $this->networkSession->getAuthenticatedUser()->ID
                );
                $SelfFollowerData->addFollowing($peer_id);
                $this->networkSession->getSocialvoidLib()->getFollowerDataManager()->updateRecord($SelfFollowerData);

                // This user got a following x
                $TargetFollowerData = $this->networkSession->getSocialvoidLib()->getFollowerDataManager()->resolveRecord(
                    $peer_id
                );
                $TargetFollowerData->addFollower($this->networkSession->getAuthenticatedUser()->ID);
                $this->networkSession->getSocialvoidLib()->getFollowerDataManager()->updateRecord($TargetFollowerData);

            }
            
            return $FollowerState;
        }

        // TODO: Add the ability to get follower and following data by IDs only rather than the whole user object

        /**
         * Gets following data of a peer
         *
         * @param $peer
         * @return FollowerData
         * @throws DatabaseException
         * @throws FollowerDataNotFound
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws PeerNotFoundException
         */
        public function getFollowerData($peer): FollowerData
        {
            // Resolve the Peer ID
            $peer_id = null;
            $cache_peer = $this->networkSession->getActiveSession()->getSessionCache()->getCachedPeer($peer);

            if($cache_peer == null)
            {
                $peer_id = $this->resolvePeer($peer)->ID;
            }
            else
            {
                $peer_id = $cache_peer->PeerID;
            }

            return $this->networkSession->getSocialvoidLib()->getFollowerDataManager()->resolveRecord($peer_id);
        }

        /**
         * Gets a list of users that are following this peer
         *
         * @param $peer
         * @param int $offset
         * @param int $limit
         * @return array
         * @throws DatabaseException
         * @throws FollowerDataNotFound
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws PeerNotFoundException
         */
        public function getFollowers($peer, int $offset=0, int $limit=100): array
        {
            $Results = [];
            $CurrentIteration = 0;
            $FollowerData = $this->getFollowerData($peer);

            foreach($FollowerData->FollowersIDs as $followersID)
            {
                if($CurrentIteration >= $offset)
                {
                    try
                    {
                        $Results[] = $this->resolvePeer($followersID, false);
                    }
                    catch(Exception $e)
                    {
                        unset($e);
                    }
                }

                if(count($Results) >= $limit)
                    break;

                $CurrentIteration += 1;
            }

            return $Results;
        }

        /**
         * Returns an array of followers via IDs
         *
         * @param $peer
         * @return array
         * @throws DatabaseException
         * @throws FollowerDataNotFound
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws PeerNotFoundException
         */
        public function getFollowerIDs($peer): array
        {
            $FollowerData = $this->getFollowerData($peer);
            return $FollowerData->FollowersIDs;
        }

        /**
         * Gets a list of users that the peer is following
         *
         * @param $peer
         * @param int $offset
         * @param int $limit
         * @return array
         * @throws DatabaseException
         * @throws FollowerDataNotFound
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws PeerNotFoundException
         */
        public function getFollowing($peer, int $offset=0, int $limit=100): array
        {
            $Results = [];
            $CurrentIteration = 0;
            $FollowerData = $this->getFollowerData($peer);

            foreach($FollowerData->FollowingIDs as $followingsID)
            {
                if($CurrentIteration >= $offset)
                {
                    try
                    {
                        $Results[] = $this->resolvePeer($followingsID, false);
                    }
                    catch(Exception $e)
                    {
                        unset($e);
                    }
                }

                if(count($Results) >= $limit)
                    break;

                $CurrentIteration += 1;
            }

            return $Results;
        }

        /**
         * Returns an array of following via IDs
         *
         * @param $peer
         * @return array
         * @throws DatabaseException
         * @throws FollowerDataNotFound
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws PeerNotFoundException
         */
        public function getFollowingIDs($peer): array
        {
            $FollowerData = $this->getFollowerData($peer);
            return $FollowerData->FollowingIDs;
        }
    }
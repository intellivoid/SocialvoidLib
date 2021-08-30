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
    use SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod;
    use SocialvoidLib\Abstracts\StatusStates\FollowerState;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Internal\FollowerDataNotFound;
    use SocialvoidLib\Exceptions\Internal\FollowerStateNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Authentication\BadSessionChallengeAnswerException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionExpiredException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPublicHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPeerInputException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\FollowerData;
    use SocialvoidLib\Objects\Standard\Peer;
    use SocialvoidLib\Objects\Standard\SessionIdentification;
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
         * @param SessionIdentification $sessionIdentification
         * @param $peer
         * @param bool $resolve_internally
         * @return Peer
         * @throws BadSessionChallengeAnswerException
         * @throws CacheException
         * @throws DatabaseException
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         */
        public function resolvePeer(SessionIdentification $sessionIdentification, $peer, bool $resolve_internally=True): User
        {
            $this->networkSession->loadSession($sessionIdentification);
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            // Probably an ID
            if((ctype_digit($peer) && $resolve_internally) || (is_int($peer) && $resolve_internally))
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

            return $peer_result;
        }

        /**
         * Follows another peer on the network
         *
         * @param SessionIdentification $sessionIdentification
         * @param $peer
         * @return string
         * @throws BadSessionChallengeAnswerException
         * @throws CacheException
         * @throws DatabaseException
         * @throws FollowerDataNotFound
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         */
        public function followPeer(SessionIdentification $sessionIdentification, $peer): string
        {
            $this->networkSession->loadSession($sessionIdentification);
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            // TODO: Update the timeline upon a follow event
            // Resolve the Peer ID
            $peer_id = $this->resolvePeer($sessionIdentification, $peer)->ID;

            try
            {
                $FollowerState = $this->networkSession->getSocialvoidLib()->getFollowerStateManager()->getFollowingState(
                    $this->networkSession->getAuthenticatedUser()->ID, $peer_id
                );

                return $FollowerState->State;
            }
            catch(FollowerStateNotFoundException $e)
            {
                unset($e);
            }

            $TargetPeer = $this->resolvePeer($sessionIdentification, $peer_id);

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
         * @param SessionIdentification $sessionIdentification
         * @param $peer
         * @return FollowerData
         * @throws BadSessionChallengeAnswerException
         * @throws CacheException
         * @throws DatabaseException
         * @throws FollowerDataNotFound
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         */
        public function getFollowerData(SessionIdentification $sessionIdentification, $peer): FollowerData
        {
            $this->networkSession->loadSession($sessionIdentification);
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            // Resolve the Peer ID
            $peer_id = $this->resolvePeer($sessionIdentification, $peer)->ID;

            return $this->networkSession->getSocialvoidLib()->getFollowerDataManager()->resolveRecord($peer_id);
        }

        /**
         * Gets a list of users that are following this peer
         *
         * @param SessionIdentification $sessionIdentification
         * @param $peer
         * @param int $offset
         * @param int $limit
         * @return array
         * @throws BadSessionChallengeAnswerException
         * @throws CacheException
         * @throws DatabaseException
         * @throws FollowerDataNotFound
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         */
        public function getFollowers(SessionIdentification $sessionIdentification, $peer, int $offset=0, int $limit=100): array
        {
            $this->networkSession->loadSession($sessionIdentification);
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $Results = [];
            $CurrentIteration = 0;
            $FollowerData = $this->getFollowerData($sessionIdentification, $peer);

            foreach($FollowerData->FollowersIDs as $followersID)
            {
                if($CurrentIteration >= $offset)
                {
                    try
                    {
                        $Results[] = $this->resolvePeer($sessionIdentification, $followersID);
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
         * @param SessionIdentification $sessionIdentification
         * @param $peer
         * @return array
         * @throws BadSessionChallengeAnswerException
         * @throws CacheException
         * @throws DatabaseException
         * @throws FollowerDataNotFound
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         */
        public function getFollowerIDs(SessionIdentification $sessionIdentification, $peer): array
        {
            $this->networkSession->loadSession($sessionIdentification);
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $FollowerData = $this->getFollowerData($sessionIdentification, $peer);
            return $FollowerData->FollowersIDs;
        }

        /**
         * Gets a list of users that the peer is following
         *
         * @param SessionIdentification $sessionIdentification
         * @param $peer
         * @param int $offset
         * @param int $limit
         * @return array
         * @throws BadSessionChallengeAnswerException
         * @throws CacheException
         * @throws DatabaseException
         * @throws FollowerDataNotFound
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         */
        public function getFollowing(SessionIdentification $sessionIdentification, $peer, int $offset=0, int $limit=100): array
        {
            $this->networkSession->loadSession($sessionIdentification);
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $Results = [];
            $CurrentIteration = 0;
            $FollowerData = $this->getFollowerData($sessionIdentification, $peer);

            foreach($FollowerData->FollowingIDs as $followingsID)
            {
                if($CurrentIteration >= $offset)
                {
                    try
                    {
                        $Results[] = $this->resolvePeer($sessionIdentification, $followingsID);
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
         * @param SessionIdentification $sessionIdentification
         * @param $peer
         * @return array
         * @throws BadSessionChallengeAnswerException
         * @throws CacheException
         * @throws DatabaseException
         * @throws FollowerDataNotFound
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         */
        public function getFollowingIDs(SessionIdentification $sessionIdentification, $peer): array
        {
            $this->networkSession->loadSession($sessionIdentification);
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $FollowerData = $this->getFollowerData($sessionIdentification, $peer);
            return $FollowerData->FollowingIDs;
        }
    }
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

    use BackgroundWorker\Exceptions\ServerNotReachableException;
    use SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod;
    use SocialvoidLib\Abstracts\StatusStates\RelationState;
    use SocialvoidLib\Abstracts\StatusStates\UserPrivacyState;
    use SocialvoidLib\Exceptions\GenericInternal\BackgroundWorkerNotEnabledException;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\DisplayPictureException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\GenericInternal\ServiceJobException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Network\BlockedByPeerException;
    use SocialvoidLib\Exceptions\Standard\Network\BlockedPeerException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\SelfInteractionNotPermittedException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidLimitValueException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidCursorValueException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPeerInputException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Standard\Profile;
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
         * @param bool $resolve_internally
         * @return User
         * @throws CacheException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws DisplayPictureException
         */
        public function resolvePeer($peer, bool $resolve_internally=True): User
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            if(gettype($peer) == 'object' && get_class($peer) == 'SocialvoidLib\Objects\User')
                return $peer; // No need to resolve an already constructed object!

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
         * @param array $peers
         * @return User[]
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws InvalidSearchMethodException
         * @throws PeerNotFoundException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @throws DisplayPictureException
         */
        public function resolveMultiplePeers(array $peers): array
        {
            return $this->networkSession->getSocialvoidLib()->getUserManager()->getMultipleUsers($peers);
        }

        /**
         * @param $peer
         * @return int
         * @throws CacheException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws DisplayPictureException
         */
        public function resolveRelation($peer): int
        {
            $target_peer = $this->resolvePeer($peer);

            $current_relation = $this->networkSession->getSocialvoidLib()->getRelationStateManager()->getState(
                $this->networkSession->getAuthenticatedUser(), $target_peer
            );
            $reverse_relation = $this->networkSession->getSocialvoidLib()->getRelationStateManager()->getState(
                $target_peer, $this->networkSession->getAuthenticatedUser()
            );

            if($current_relation == RelationState::None && $reverse_relation == RelationState::None)
                return RelationState::None;

            if($current_relation == RelationState::Blocked)
                return RelationState::Blocked;

            if($reverse_relation == RelationState::Blocked)
                return RelationState::BlockedYou;

            if($current_relation == RelationState::Following && $reverse_relation == RelationState::Following)
                return RelationState::MutuallyFollowing;

            if($reverse_relation == RelationState::Following)
                return RelationState::FollowsYou;

            return $current_relation;
        }

        /**
         * Follows another peer on the network
         *
         * @param $peer
         * @return int
         * @throws BlockedByPeerException
         * @throws BlockedPeerException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SelfInteractionNotPermittedException
         */
        public function followPeer($peer): int
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            // TODO: Update the timeline upon a follow event
            // Resolve the Peer ID
            $target_peer = $this->resolvePeer($peer);

            if($target_peer->ID == $this->networkSession->getAuthenticatedUser()->ID)
                throw new SelfInteractionNotPermittedException('You cannot follow a peer that you are authenticated as');

            $relationship = $this->resolveRelation($target_peer);

            if($relationship == RelationState::BlockedYou)
                throw new BlockedByPeerException();
            if($relationship == RelationState::Blocked)
                throw new BlockedPeerException();
            if($relationship == RelationState::Following || $relationship == RelationState::MutuallyFollowing)
                return $relationship;

            if($target_peer->PrivacyState == UserPrivacyState::Private)
            {
                $this->networkSession->getSocialvoidLib()->getRelationStateManager()->registerState(
                    $this->networkSession->getAuthenticatedUser(), $target_peer, RelationState::AwaitingApproval
                );

                return RelationState::AwaitingApproval;
            }

            $this->networkSession->getSocialvoidLib()->getRelationStateManager()->registerState(
                $this->networkSession->getAuthenticatedUser(), $target_peer, RelationState::Following
            );

            return RelationState::Following;
        }

        /**
         * Unfollows the requested peer
         *
         * @param $peer
         * @return int
         * @throws BlockedByPeerException
         * @throws BlockedPeerException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws DisplayPictureException
         */
        public function unfollowPeer($peer): int
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            // TODO: Update the timeline upon a follow event
            // Resolve the Peer ID
            $target_peer = $this->resolvePeer($peer);
            $relationship = $this->resolveRelation($target_peer);

            if($relationship == RelationState::BlockedYou)
                throw new BlockedByPeerException();
            if($relationship == RelationState::Blocked)
                throw new BlockedPeerException();
            if($relationship == RelationState::None)
                return $relationship;

            $this->networkSession->getSocialvoidLib()->getRelationStateManager()->registerState(
                $this->networkSession->getAuthenticatedUser(), $target_peer, RelationState::None
            );

            return RelationState::None;
        }

        /**
         * Returns a standard profile object of the requested peer
         *
         * @param $peer
         * @return Profile
         * @throws CacheException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws DisplayPictureException
         */
        public function getProfile($peer): Profile
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            // Resolve the Peer ID
            $target_peer = $this->resolvePeer($peer);

            $stdProfile = Profile::fromUser($target_peer);
            $stdProfile->FollowersCount = $this->networkSession->getSocialvoidLib()->getRelationStateManager()->getFollowersCount($target_peer);
            $stdProfile->FollowingCount = $this->networkSession->getSocialvoidLib()->getRelationStateManager()->getFollowingCount($target_peer);

            return $stdProfile;
        }

        /**
         * Returns an array of resolved users from followers
         *
         * @param $peer
         * @param int $limit
         * @param int $offset
         * @return User[]
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws InvalidLimitValueException
         * @throws InvalidCursorValueException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @noinspection DuplicatedCode
         */
        public function getFollowers($peer, int $limit, int $offset): array
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            if($offset < 0)
                throw new InvalidCursorValueException('The offset value cannot be a negative value');
            if($limit < 1)
                throw new InvalidLimitValueException('The limit value must be a value greater than 0');
            if($limit > (int)$this->networkSession->getSocialvoidLib()->getMainConfiguration()['RetrieveFollowersMixLimit'])
                throw new InvalidLimitValueException('The limit value cannot exceed ' . $this->networkSession->getSocialvoidLib()->getMainConfiguration()['RetrieveFollowersMixLimit']);

            // Resolve the Peer ID
            $target_peer = $this->resolvePeer($peer);
            $search_query = [];
            $followers_ids = $this->networkSession->getSocialvoidLib()->getRelationStateManager()->getFollowers($target_peer, $limit, $offset);

            foreach($followers_ids as $user_id)
                $search_query[$user_id] = UserSearchMethod::ById;

            $resolvedPeers = $this->resolveMultiplePeers($search_query);
            $return_results = [];

            // Sort the results
            foreach($followers_ids as $followers_id)
            {
                foreach($resolvedPeers as $resolvedPeer)
                {
                    if($resolvedPeer->ID == $followers_id)
                    {
                        $return_results[] = $resolvedPeer;
                        break;
                    }
                }
            }

            return $return_results;
        }

        /**
         * Returns an array of users that this user is following
         *
         * @param $peer
         * @param int $limit
         * @param int $offset
         * @return User[]
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         * @throws DisplayPictureException
         * @noinspection DuplicatedCode
         */
        public function getFollowing($peer, int $limit, int $offset): array
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            if($offset < 0)
                throw new InvalidCursorValueException('The offset value cannot be a negative value');
            if($limit < 1)
                throw new InvalidLimitValueException('The limit value must be a value greater than 0');
            if($limit > (int)$this->networkSession->getSocialvoidLib()->getMainConfiguration()['RetrieveFollowingMixLimit'])
                throw new InvalidLimitValueException('The limit value cannot exceed ' . $this->networkSession->getSocialvoidLib()->getMainConfiguration()['RetrieveFollowingMixLimit']);

            // Resolve the Peer ID
            $target_peer = $this->resolvePeer($peer);
            $search_query = [];
            $following_ids = $this->networkSession->getSocialvoidLib()->getRelationStateManager()->getFollowing($target_peer, $limit, $offset);

            foreach($following_ids as $user_id)
                $search_query[$user_id] = UserSearchMethod::ById;

            $resolvedPeers = $this->resolveMultiplePeers($search_query);
            $return_results = [];

            // Sort the results
            foreach($following_ids as $followers_id)
            {
                foreach($resolvedPeers as $resolvedPeer)
                {
                    if($resolvedPeer->ID == $followers_id)
                    {
                        $return_results[] = $resolvedPeer;
                        break;
                    }
                }
            }

            return $return_results;
        }
    }
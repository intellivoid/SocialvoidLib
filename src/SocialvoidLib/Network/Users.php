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
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\GenericInternal\ServiceJobException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Network\BlockedByPeerException;
    use SocialvoidLib\Exceptions\Standard\Network\BlockedPeerException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPeerInputException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\User;
    use udp2\Exceptions\AvatarGeneratorException;
    use udp2\Exceptions\AvatarNotFoundException;
    use udp2\Exceptions\ImageTooSmallException;
    use udp2\Exceptions\UnsupportedAvatarGeneratorException;
    use Zimage\Exceptions\CannotGetOriginalImageException;
    use Zimage\Exceptions\FileNotFoundException;
    use Zimage\Exceptions\InvalidZimageFileException;
    use Zimage\Exceptions\SizeNotSetException;
    use Zimage\Exceptions\UnsupportedImageTypeException;

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
         * @throws AvatarGeneratorException
         * @throws AvatarNotFoundException
         * @throws CacheException
         * @throws CannotGetOriginalImageException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws FileNotFoundException
         * @throws ImageTooSmallException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws InvalidZimageFileException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SizeNotSetException
         * @throws UnsupportedAvatarGeneratorException
         * @throws UnsupportedImageTypeException
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
         * @throws AvatarGeneratorException
         * @throws AvatarNotFoundException
         * @throws CacheException
         * @throws CannotGetOriginalImageException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws FileNotFoundException
         * @throws ImageTooSmallException
         * @throws InvalidSearchMethodException
         * @throws InvalidZimageFileException
         * @throws PeerNotFoundException
         * @throws SizeNotSetException
         * @throws UnsupportedAvatarGeneratorException
         * @throws UnsupportedImageTypeException
         * @throws BackgroundWorkerNotEnabledException
         * @throws ServiceJobException
         * @throws ServerNotReachableException
         * @throws ServerNotReachableException
         */
        public function resolveMultiplePeers(array $peers): array
        {
            return $this->networkSession->getSocialvoidLib()->getUserManager()->getMultipleUsers($peers);
        }

        /**
         * @throws UnsupportedAvatarGeneratorException
         * @throws CannotGetOriginalImageException
         * @throws InvalidSearchMethodException
         * @throws PeerNotFoundException
         * @throws AvatarNotFoundException
         * @throws NotAuthenticatedException
         * @throws AvatarGeneratorException
         * @throws SizeNotSetException
         * @throws DocumentNotFoundException
         * @throws ImageTooSmallException
         * @throws InvalidZimageFileException
         * @throws InvalidPeerInputException
         * @throws UnsupportedImageTypeException
         * @throws CacheException
         * @throws DatabaseException
         * @throws FileNotFoundException
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
         * @throws AvatarGeneratorException
         * @throws AvatarNotFoundException
         * @throws BlockedByPeerException
         * @throws CacheException
         * @throws CannotGetOriginalImageException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws FileNotFoundException
         * @throws ImageTooSmallException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws InvalidZimageFileException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SizeNotSetException
         * @throws UnsupportedAvatarGeneratorException
         * @throws UnsupportedImageTypeException
         * @throws BlockedPeerException
         */
        public function followPeer($peer): int
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
         * @throws AvatarGeneratorException
         * @throws AvatarNotFoundException
         * @throws BlockedByPeerException
         * @throws BlockedPeerException
         * @throws CacheException
         * @throws CannotGetOriginalImageException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws FileNotFoundException
         * @throws ImageTooSmallException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws InvalidZimageFileException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SizeNotSetException
         * @throws UnsupportedAvatarGeneratorException
         * @throws UnsupportedImageTypeException
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

    }
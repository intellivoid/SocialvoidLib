<?php


    namespace SocialvoidLib\Network;

    use SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Standard\Network\InvalidPeerInputException;
    use SocialvoidLib\Exceptions\Standard\Network\UserNotFoundException;
    use SocialvoidLib\NetworkSession;
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
         * @throws UserNotFoundException
         */
        public function resolvePeer($peer, bool $cache_session=True): User
        {
            // Probably an ID
            if(ctype_digit($peer))
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
            elseif(strlen($peer) == 256)
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
                $this->networkSession->getActiveSession()->SessionCache->cachePeer($peer);
                $this->networkSession->updateActiveSession();
            }

            return $peer_result;
        }
    }
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
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib;

    use Exception;
    use SocialvoidLib\Abstracts\Flags\NetworkFlags;
    use SocialvoidLib\Abstracts\SearchMethods\ActiveSessionSearchMethod;
    use SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod;
    use SocialvoidLib\Abstracts\UserAuthenticationMethod;
    use SocialvoidLib\Classes\Converter;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\Standard\Authentication\AlreadyAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Authentication\AuthenticationFailureException;
    use SocialvoidLib\Exceptions\Standard\Authentication\IncorrectLoginCredentialsException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionExpiredException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\InputTypes\SessionClient;
    use SocialvoidLib\Network\Cloud;
    use SocialvoidLib\Network\Timeline;
    use SocialvoidLib\Network\Users;
    use SocialvoidLib\Objects\ActiveSession;
    use SocialvoidLib\Objects\Standard\Peer;
    use SocialvoidLib\Objects\Standard\SessionEstablished;
    use SocialvoidLib\Objects\Standard\SessionIdentification;
    use SocialvoidLib\Objects\User;

    /**
     * Class Network
     * @package SocialvoidLib
     */
    class NetworkSession
    {
        // TODO: Add auto-update for ActiveSession
        // TODO: Add auto-update for AuthenticatedUser
        // TODO: Add function for loading and dumping the network session to a file

        /**
         * Flags associated with this network session
         *
         * @var array
         */
        private $flags;

        /**
         * The current active session on the network
         *
         * @var ActiveSession|null
         */
        public $active_session;

        /**
         * The current user that's currently authenticated
         *
         * @var User|null
         */
        public $authenticated_user;

        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * @var Users
         */
        private Users $users;

        /**
         * @var Timeline
         */
        private Timeline $timeline;

        /**
         * @var Cloud
         */
        private Cloud $cloud;

        /**
         * @var Cloud
         */
        //private Cloud $cloud;

        /**
         * Network constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->flags = [];
            $this->socialvoidLib = $socialvoidLib;
            $this->cloud = new Cloud($this);
            $this->users = new Users($this);
            $this->timeline = new Timeline($this);
        }

        /**
         * Creates a new session
         * @param SessionClient $sessionClient
         * @param string $ip_address
         * @return SessionEstablished
         * @throws Exceptions\Standard\Validation\InvalidClientNameException
         * @throws Exceptions\Standard\Validation\InvalidClientPrivateHashException
         * @throws Exceptions\Standard\Validation\InvalidClientPublicHashException
         * @throws Exceptions\Standard\Validation\InvalidPlatformException
         * @throws Exceptions\Standard\Validation\InvalidVersionException
         * @throws InternalServerException
         */
        public function createSession(SessionClient $sessionClient, string $ip_address): SessionEstablished
        {
            $sessionClient->validate();

            try
            {
                $session_established = $this->socialvoidLib->getSessionManager()->createSession($sessionClient, $ip_address);
            }
            catch(Exception $e)
            {
                throw new InternalServerException("There was an unexpected error while trying establish a session", $e);
            }


            return $session_established;
        }

        /**
         * Loads a session from a session identification
         *
         * @param SessionIdentification $sessionIdentification
         * @throws Exceptions\GenericInternal\CacheException
         * @throws Exceptions\GenericInternal\DatabaseException
         * @throws Exceptions\GenericInternal\InvalidSearchMethodException
         * @throws Exceptions\Standard\Authentication\BadSessionChallengeAnswerException
         * @throws Exceptions\Standard\Authentication\SessionNotFoundException
         * @throws Exceptions\Standard\Validation\InvalidClientPublicHashException
         * @throws InternalServerException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         */
        public function loadSession(SessionIdentification $sessionIdentification)
        {
            $sessionIdentification->validate();

            // Reload the session if it's a mismatch (Saves resources)
            if($this->active_session == null || $this->active_session->ID !== $sessionIdentification->SessionID)
            {
                try
                {
                    $this->active_session = $this->socialvoidLib->getSessionManager()->getSession(ActiveSessionSearchMethod::ById, $sessionIdentification->SessionID);
                }
                catch (Exception $e)
                {
                    if(Validate::isStandardError($e->getCode()))
                        /** @noinspection PhpUnhandledExceptionInspection */
                        throw $e;

                    throw new InternalServerException("There was an error while trying to access the session", $e);
                }
            }

            // Always validate the challenge answer!
            $sessionIdentification->validateAnswer($this->active_session->Security->ClientPrivateHash, $this->active_session->Security->HashChallenge);

            if(time() > $this->active_session->ExpiresTimestamp)
                throw new SessionExpiredException("The session has expired");

            // Update the session if it hasn't been updated in more than 3 minutes
            if((time() - $this->active_session->LastActiveTimestamp) > 300)
            {
                try
                {
                    $this->active_session = $this->socialvoidLib->getSessionManager()->updateSession($this->active_session);
                }
                catch(Exception $e)
                {
                    throw new InternalServerException("There was an error while trying to make changes to the session", $e);
                }
            }

            if($this->active_session->isAuthenticated() && $this->active_session->UserID !== null)
            {
                $this->loadAuthenticatedPeer();
            }
        }

        /**
         * Loads the authenticated peer into the network session
         *
         * @throws Exceptions\GenericInternal\CacheException
         * @throws Exceptions\GenericInternal\DatabaseException
         * @throws Exceptions\GenericInternal\InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         */
        public function loadAuthenticatedPeer()
        {
            if($this->active_session == null)
                throw new NotAuthenticatedException("You must be authenticated to preform this action");

            if($this->authenticated_user !== null && $this->authenticated_user->ID !== $this->active_session->UserID)
                return;

            if($this->active_session->Authenticated && $this->active_session->UserID !== null)
            {
                $this->authenticated_user = $this->socialvoidLib->getUserManager()->getUser(UserSearchMethod::ById, $this->active_session->UserID);
                Converter::addFlag($this->flags, NetworkFlags::Authenticated);
            }
        }

        /**
         * Logs the current user out of the session
         *
         * @param SessionIdentification $sessionIdentification
         * @throws Exceptions\GenericInternal\DatabaseException
         * @throws Exceptions\GenericInternal\InvalidSearchMethodException
         * @throws Exceptions\Standard\Authentication\BadSessionChallengeAnswerException
         * @throws Exceptions\Standard\Authentication\SessionNotFoundException
         * @throws Exceptions\Standard\Validation\InvalidClientPublicHashException
         * @throws InternalServerException
         * @throws NotAuthenticatedException
         * @throws SessionExpiredException
         * @throws Exceptions\GenericInternal\CacheException
         * @throws PeerNotFoundException
         */
        public function logout(SessionIdentification $sessionIdentification)
        {
            $this->loadSession($sessionIdentification);

            if($this->active_session->Authenticated == false)
                throw new NotAuthenticatedException("You must be authenticated to preform this action");

            $this->active_session->Authenticated = false;
            $this->active_session->UserID = null;
            $this->active_session->AuthenticationMethodUsed = UserAuthenticationMethod::None;

            $this->authenticated_user = null;

            Converter::removeFlag($this->flags, NetworkFlags::Authenticated);

            try
            {
                $this->socialvoidLib->getSessionManager()->updateSession($this->active_session);
            }
            catch (Exceptions\GenericInternal\DatabaseException $e)
            {
                throw new InternalServerException("There was an error while trying to process your request", $e);
            }
        }

        /**
         * Authenticates the user to the network session, updates both the user and session
         *
         * @param SessionIdentification $sessionIdentification
         * @param string $username
         * @param string $password
         * @param string|null $otp
         * @return bool
         * @throws AlreadyAuthenticatedException
         * @throws AuthenticationFailureException
         * @throws Exceptions\GenericInternal\CacheException
         * @throws Exceptions\GenericInternal\DatabaseException
         * @throws Exceptions\GenericInternal\InvalidSearchMethodException
         * @throws Exceptions\Standard\Authentication\AuthenticationNotApplicableException
         * @throws Exceptions\Standard\Authentication\BadSessionChallengeAnswerException
         * @throws Exceptions\Standard\Authentication\IncorrectTwoFactorAuthenticationCodeException
         * @throws Exceptions\Standard\Authentication\PrivateAccessTokenRequiredException
         * @throws Exceptions\Standard\Authentication\SessionNotFoundException
         * @throws Exceptions\Standard\Authentication\TwoFactorAuthenticationRequiredException
         * @throws Exceptions\Standard\Validation\InvalidClientPublicHashException
         * @throws IncorrectLoginCredentialsException
         * @throws InternalServerException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws Exceptions\Internal\NoPasswordAuthenticationAvailableException
         * @throws NotAuthenticatedException
         */
        public function authenticateUser(SessionIdentification $sessionIdentification, string $username, string $password, ?string $otp=null): bool
        {
            $this->loadSession($sessionIdentification);

            if($this->active_session->Authenticated)
                throw new AlreadyAuthenticatedException("You are already authenticated to the network");

            if(Validate::username($username) == false)
                throw new IncorrectLoginCredentialsException("The given password or username is incorrect");

            if(Validate::password($password) == false)
                throw new IncorrectLoginCredentialsException("The given password or username is incorrect");

            try
            {
                $authenticating_peer = $this->socialvoidLib->getUserManager()->getUser(UserSearchMethod::ByUsername, $username);
            }
            catch (PeerNotFoundException $e)
            {
                throw new IncorrectLoginCredentialsException("The given password or username is incorrect", $e);
            }

            try
            {
                $authenticating_peer->simpleAuthentication($password, $otp);
            }
            catch (Exceptions\Internal\AuthenticationFailureException $e)
            {
                throw new AuthenticationFailureException("There was an unexpected error while trying to authenticate the user (-s2)", $e);
            }

            try
            {
                $this->socialvoidLib->getUserManager()->updateUser($authenticating_peer);
            }
            catch(Exception $e)
            {
                if(Validate::isStandardError($e->getCode()))
                    /** @noinspection PhpUnhandledExceptionInspection */
                    throw $e;

                throw new InternalServerException("There was an error while trying to process your request", $e);
            }

            $this->active_session->Authenticated = true;
            $this->active_session->UserID = $authenticating_peer->ID;
            $this->active_session->AuthenticationMethodUsed = $authenticating_peer->AuthenticationMethod;

            try
            {
                $this->socialvoidLib->getSessionManager()->updateSession($this->active_session);
            }
            catch (Exceptions\GenericInternal\DatabaseException $e)
            {
                throw new InternalServerException("There was an error while trying to process your request", $e);
            }

            Converter::addFlag($this->flags, NetworkFlags::Authenticated);

            return true;
        }

        /**
         * Registers a new peer to the network
         *
         * @param SessionIdentification $sessionIdentification
         * @param string $username
         * @param string $password
         * @param string $first_name
         * @param string|null $last_name
         * @return Peer
         * @throws AlreadyAuthenticatedException
         * @throws Exceptions\GenericInternal\CacheException
         * @throws Exceptions\GenericInternal\DatabaseException
         * @throws Exceptions\GenericInternal\InvalidSearchMethodException
         * @throws Exceptions\Standard\Authentication\BadSessionChallengeAnswerException
         * @throws Exceptions\Standard\Authentication\SessionNotFoundException
         * @throws Exceptions\Standard\Validation\InvalidClientPublicHashException
         * @throws Exceptions\Standard\Validation\InvalidFirstNameException
         * @throws Exceptions\Standard\Validation\InvalidLastNameException
         * @throws Exceptions\Standard\Validation\InvalidPasswordException
         * @throws Exceptions\Standard\Validation\InvalidUsernameException
         * @throws Exceptions\Standard\Validation\UsernameAlreadyExistsException
         * @throws InternalServerException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws NotAuthenticatedException
         */
        public function registerUser(SessionIdentification $sessionIdentification, string $username, string $password, string $first_name, ?string $last_name=null): Peer
        {
            $this->loadSession($sessionIdentification);

            if($this->active_session->Authenticated)
                throw new AlreadyAuthenticatedException("You are already authenticated to the network");

            try
            {
                $registered_peer = $this->socialvoidLib->getUserManager()->registerUser($username, $first_name, $last_name);
            }
            catch(Exception $e)
            {
                if(Validate::isStandardError($e->getCode()))
                    /** @noinspection PhpUnhandledExceptionInspection */
                    throw $e;

                throw new InternalServerException("There was an error while trying to register the peer", $e);
            }

            $registered_peer->disableAllAuthenticationMethods();
            $registered_peer->AuthenticationProperties->setPassword($password);
            $registered_peer->AuthenticationMethod = UserAuthenticationMethod::Simple;

            try
            {
                $this->socialvoidLib->getUserManager()->updateUser($registered_peer);
            }
            catch(Exception $e)
            {
                if(Validate::isStandardError($e->getCode()))
                    /** @noinspection PhpUnhandledExceptionInspection */
                    throw $e;

                throw new InternalServerException("There was an error while trying to update the registered peer", $e);
            }

            return Peer::fromUser($registered_peer);
        }

        /**
         * Indicates if the user is currently authenticated
         *
         * @return bool
         */
        public function isAuthenticated(): bool
        {
            if($this->active_session == null)
                return false;

            return Converter::hasFlag($this->flags, NetworkFlags::Authenticated);
        }

        /**
         * Returns the current authenticated user on the network
         *
         * @return User|null
         */
        public function getAuthenticatedUser(): ?User
        {
            return $this->authenticated_user;
        }

        /**
         * Returns the current active session
         *
         * @return ActiveSession|null
         */
        public function getActiveSession(): ?ActiveSession
        {
            return $this->active_session;
        }

        /**
         * Updates the current active session
         *
         * @throws Exceptions\GenericInternal\DatabaseException
         * @throws Exceptions\GenericInternal\CacheException
         */
        public function updateActiveSession(): void
        {
            $this->active_session = $this->socialvoidLib->getSessionManager()->updateSession(
                $this->active_session
            );
        }

        /**
         * Returns the current flags on the network
         *
         * @return array
         */
        public function getFlags(): array
        {
            return $this->flags;
        }

        /**
         * Returns an array representation of the current network session
         *
         * @return array
         */
        public function dumpNetworkSession(): array
        {
            return [
                "flags" => $this->flags,
                "active_session" => $this->active_session->toArray(),
                "authenticated_user" => $this->authenticated_user->toArray()
            ];
        }

        /**
         * Constructs a network session from an array representation
         *
         * @param array $data
         * @param SocialvoidLib $socialvoidLib
         * @return NetworkSession
         */
        public static function loadFromSession(array $data, SocialvoidLib $socialvoidLib): NetworkSession
        {
            $NetworkSessionObject = new NetworkSession($socialvoidLib);

            if(isset($data["flags"]))
                $NetworkSessionObject->flags = $data["flags"];

            if(isset($data["active_session"]))
                $NetworkSessionObject->active_session = ActiveSession::fromArray($data["active_session"]);

            if(isset($data["authenticated_user"]))
                $NetworkSessionObject->authenticated_user = User::fromArray($data["authenticated_user"]);

            return $NetworkSessionObject;
        }

        /**
         * @return SocialvoidLib
         */
        public function getSocialvoidLib(): SocialvoidLib
        {
            return $this->socialvoidLib;
        }

        /**
         * @param User|null $authenticated_user
         */
        public function setAuthenticatedUser(?User $authenticated_user): void
        {
            $this->authenticated_user = $authenticated_user;
        }

        /**
         * @return Users
         */
        public function getUsers(): Users
        {
            return $this->users;
        }

        /**
         * @return Timeline
         */
        public function getTimeline(): Timeline
        {
            return $this->timeline;
        }

        /**
         * @return Cloud
         */
        public function getCloud(): Cloud
        {
            return $this->cloud;
        }
    }
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
    use SocialvoidLib\Classes\Converter;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\Internal\AlreadyAuthenticatedToNetwork;
    use SocialvoidLib\Exceptions\Standard\Authentication\AuthenticationFailureException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\InputTypes\SessionClient;
    use SocialvoidLib\Network\Cloud;
    use SocialvoidLib\Network\Timeline;
    use SocialvoidLib\Network\Users;
    use SocialvoidLib\Objects\ActiveSession;
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
        //private Cloud $cloud;

        /**
         * Network constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->flags = [];
            $this->socialvoidLib = $socialvoidLib;
            //$this->cloud = new Cloud($this);
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
         * Authenticates the user to the network session, updates both the user and session
         *
         * @param SessionIdentification $sessionIdentification
         * @param string $username
         * @param string $password
         * @param string|null $otp
         * @return bool
         * @throws AlreadyAuthenticatedToNetwork
         * @throws AuthenticationFailureException
         * @throws Exceptions\GenericInternal\CacheException
         * @throws Exceptions\GenericInternal\DatabaseException
         * @throws Exceptions\GenericInternal\InvalidSearchMethodException
         * @throws Exceptions\Internal\TwoFactorAuthenticationRequiredException
         * @throws Exceptions\Standard\Authentication\AuthenticationNotApplicableException
         * @throws Exceptions\Standard\Authentication\BadSessionChallengeAnswerException
         * @throws Exceptions\Standard\Authentication\IncorrectPasswordException
         * @throws Exceptions\Standard\Authentication\IncorrectTwoFactorAuthenticationCodeException
         * @throws Exceptions\Standard\Authentication\NoPasswordAuthenticationAvailableException
         * @throws Exceptions\Standard\Authentication\PrivateAccessTokenRequiredException
         * @throws Exceptions\Standard\Authentication\SessionNotFoundException
         * @throws Exceptions\Standard\Validation\InvalidClientPublicHashException
         * @throws InternalServerException
         */
        public function authenticateUser(SessionIdentification $sessionIdentification, string $username, string $password, ?string $otp=null): bool
        {
            if($this->isAuthenticated())
                throw new AlreadyAuthenticatedToNetwork("There is a user already authenticated to this network session", $this);

            $sessionIdentification->validate();
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

            $sessionIdentification->validateAnswer($this->active_session->Security->ClientPrivateHash, $this->active_session->Security->HashChallenge);

            try
            {
                $authenticating_peer = $this->socialvoidLib->getUserManager()->getUser(UserSearchMethod::ByUsername, $username);
            }
            catch (Exceptions\Standard\Network\PeerNotFoundException $e)
            {
                throw new AuthenticationFailureException("There was an unexpected error while trying to authenticate the user", $e);
            }

            try
            {
                $authenticating_peer->simpleAuthentication($password, $otp);
            }
            catch (Exceptions\Internal\AuthenticationFailureException $e)
            {
                throw new AuthenticationFailureException("There was an unexpected error while trying to authenticate the user", $e);
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
<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */
    /** @noinspection PhpUnusedPrivateFieldInspection */

    namespace SocialvoidLib;

    use SocialvoidLib\Abstracts\Flags\NetworkFlags;
    use SocialvoidLib\Abstracts\Flags\UserFlags;
    use SocialvoidLib\Abstracts\SearchMethods\ActiveSessionSearchMethod;
    use SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod;
    use SocialvoidLib\Classes\Converter;
    use SocialvoidLib\Exceptions\Internal\AlreadyAuthenticatedToNetwork;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Network\SessionNoLongerAuthenticatedException;
    use SocialvoidLib\InputTypes\SessionClient;
    use SocialvoidLib\InputTypes\SessionDevice;
    use SocialvoidLib\Network\Users;
    use SocialvoidLib\Objects\ActiveSession;
    use SocialvoidLib\Objects\User;

    /**
     * Class Network
     * @package SocialvoidLib
     */
    class NetworkSession
    {
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
        private $active_session;

        /**
         * The current user that's currently authenticated
         *
         * @var User|null
         */
        private $authenticated_user;

        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * @var Users
         */
        private Users $users;

        /**
         * Network constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->flags = [];
            $this->socialvoidLib = $socialvoidLib;
            $this->users = new Users($this);
        }

        /**
         * Authenticates the user into the network and establishes an active session
         *
         * @param SessionClient $sessionClient
         * @param SessionDevice $sessionDevice
         * @param User $user
         * @param string $authentication_method_used
         * @param string $ip_address
         * @return ActiveSession
         * @throws AlreadyAuthenticatedToNetwork
         * @throws Exceptions\GenericInternal\DatabaseException
         * @throws Exceptions\GenericInternal\InvalidSearchMethodException
         * @throws Exceptions\Standard\Network\SessionNotFoundException
         * @throws Exceptions\Standard\Network\UserNotFoundException
         * @throws SessionNoLongerAuthenticatedException
         */
        public function authenticateUser(SessionClient $sessionClient, SessionDevice $sessionDevice,
                                         User $user, string $authentication_method_used, string $ip_address): ActiveSession
        {
            if($this->isAuthenticated())
            {
                throw new AlreadyAuthenticatedToNetwork("There is a user already authenticated to this network session", $this);
            }

            $active_session_id = $this->socialvoidLib->getSessionManager()->createSession(
                $sessionClient, $sessionDevice, $user,
                $authentication_method_used, $ip_address
            );
            $this->declareActiveSession($active_session_id, $ip_address);
            return $this->active_session;
        }


        /**
         * Declares the current active session on the network
         *
         * @param string $session_public_id
         * @param string|null $ip_address
         * @throws AlreadyAuthenticatedToNetwork
         * @throws Exceptions\GenericInternal\DatabaseException
         * @throws Exceptions\GenericInternal\InvalidSearchMethodException
         * @throws Exceptions\Standard\Network\SessionNotFoundException
         * @throws SessionNoLongerAuthenticatedException
         * @throws Exceptions\Standard\Network\UserNotFoundException
         */
        public function declareActiveSession(string $session_public_id, string $ip_address=null): void
        {
            if($this->isAuthenticated())
            {
                throw new AlreadyAuthenticatedToNetwork("There is a user already authenticated to this network session", $this);
            }

            $active_session = $this->socialvoidLib->getSessionManager()->getSession(
                ActiveSessionSearchMethod::ByPublicId, $session_public_id
            );

            if($active_session->Authenticated == false)
            {
                throw new SessionNoLongerAuthenticatedException("The requested session is no longer authenticated", $active_session);
            }

            if($active_session->IpAddress !== $ip_address)
            {
                $active_session->IpAddress = $ip_address;
                $active_session = $this->socialvoidLib->getSessionManager()->updateSession($active_session);
            }

            $this->active_session = $active_session;
            $this->authenticated_user = $this->socialvoidLib->getUserManager()->getUser(
                UserSearchMethod::ById, $active_session->UserID
            );

            Converter::addFlag($this->flags, NetworkFlags::Authenticated);

            if(Converter::hasFlag($active_session->Flags, UserFlags::Administrator))
            {
                Converter::addFlag($this->flags, NetworkFlags::AdministratorAccess);
            }
            elseif(Converter::hasFlag($active_session->Flags, UserFlags::Moderator))
            {
                Converter::addFlag($this->flags, NetworkFlags::ModeratorAccess);
            }
        }

        /**
         * Destroys the current session
         *
         * @throws Exceptions\GenericInternal\DatabaseException
         * @throws NotAuthenticatedException
         */
        public function logout(): void
        {
            if($this->isAuthenticated() == false)
            {
                throw new NotAuthenticatedException("You cannot logout when there are no active sessions");
            }

            $this->active_session->Authenticated = false;
            $this->socialvoidLib->getSessionManager()->updateSession($this->active_session);
            $this->active_session = null;
            $this->authenticated_user = null;

            // Remove authentication flags
            Converter::removeFlag($this->flags, NetworkFlags::Authenticated);
            Converter::removeFlag($this->flags, NetworkFlags::AdministratorAccess);
            Converter::removeFlag($this->flags, NetworkFlags::ModeratorAccess);
        }

        /**
         * Indicates if the user is currently authenticated
         *
         * @return bool
         */
        public function isAuthenticated(): bool
        {
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
         * @return Users
         */
        public function getUsers(): Users
        {
            return $this->users;
        }

        /**
         * @return SocialvoidLib
         */
        public function getSocialvoidLib(): SocialvoidLib
        {
            return $this->socialvoidLib;
        }
    }
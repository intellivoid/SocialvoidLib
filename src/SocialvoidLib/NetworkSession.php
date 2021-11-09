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
    use SocialvoidLib\Exceptions\Standard\Authentication\AuthenticationNotApplicableException;
    use SocialvoidLib\Exceptions\Standard\Authentication\BadSessionChallengeAnswerException;
    use SocialvoidLib\Exceptions\Standard\Authentication\IncorrectLoginCredentialsException;
    use SocialvoidLib\Exceptions\Standard\Authentication\IncorrectTwoFactorAuthenticationCodeException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Authentication\PrivateAccessTokenRequiredException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionExpiredException;
    use SocialvoidLib\Exceptions\Standard\Authentication\TwoFactorAuthenticationRequiredException;
    use SocialvoidLib\Exceptions\Standard\Network\AccessDeniedException;
    use SocialvoidLib\Exceptions\Standard\Network\AlreadyRepostedException;
    use SocialvoidLib\Exceptions\Standard\Network\BlockedByPeerException;
    use SocialvoidLib\Exceptions\Standard\Network\BlockedPeerException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\FileUploadException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PostDeletedException;
    use SocialvoidLib\Exceptions\Standard\Network\PostNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\SelfInteractionNotPermittedException;
    use SocialvoidLib\Exceptions\Standard\Server\DocumentUploadException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\Exceptions\Standard\Validation\AgreementRequiredException;
    use SocialvoidLib\Exceptions\Standard\Validation\FileTooLargeException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidAttachmentsException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidBiographyException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPrivateHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPublicHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidFileForProfilePictureException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidFileNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidGeoLocationException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidHelpDocumentId;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidLastNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPageValueException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPasswordException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPeerInputException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPlatformException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPostTextException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidSessionIdentificationException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidUrlValueException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidUsernameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidVersionException;
    use SocialvoidLib\Exceptions\Standard\Validation\TooManyAttachmentsException;
    use SocialvoidLib\Exceptions\Standard\Validation\UsernameAlreadyExistsException;
    use SocialvoidLib\InputTypes\SessionClient;
    use SocialvoidLib\Network\Account;
    use SocialvoidLib\Network\Cloud;
    use SocialvoidLib\Network\Timeline;
    use SocialvoidLib\Network\Users;
    use SocialvoidLib\Objects\ActiveSession;
    use SocialvoidLib\Objects\ProtocolDefinitions;
    use SocialvoidLib\Objects\Standard\HelpDocument;
    use SocialvoidLib\Objects\Standard\Peer;
    use SocialvoidLib\Objects\Standard\ServerInformation;
    use SocialvoidLib\Objects\Standard\SessionEstablished;
    use SocialvoidLib\Objects\Standard\SessionIdentification;
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
         * @var Account
         */
        private Account $account;

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
            $this->account = new Account($this);
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
         * @throws Exceptions\GenericInternal\DisplayPictureException
         * @throws Exceptions\GenericInternal\InvalidSearchMethodException
         * @throws Exceptions\Standard\Authentication\BadSessionChallengeAnswerException
         * @throws Exceptions\Standard\Authentication\SessionNotFoundException
         * @throws Exceptions\Standard\Network\DocumentNotFoundException
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
         * @throws Exceptions\GenericInternal\DisplayPictureException
         * @throws Exceptions\GenericInternal\InvalidSearchMethodException
         * @throws Exceptions\Standard\Network\DocumentNotFoundException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         */
        public function loadAuthenticatedPeer()
        {
            if($this->active_session == null)
                throw new NotAuthenticatedException("You must be authenticated to perform this action");

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
         * @throws Exceptions\GenericInternal\CacheException
         * @throws Exceptions\Standard\Authentication\SessionNotFoundException
         * @throws InternalServerException
         * @throws NotAuthenticatedException
         */
        public function logout()
        {
            if($this->active_session->Authenticated == false)
                throw new NotAuthenticatedException("You must be authenticated to perform this action");

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
         * @param string $username
         * @param string $password
         * @param string|null $otp
         * @return bool
         * @throws AlreadyAuthenticatedException
         * @throws AuthenticationFailureException
         * @throws Exceptions\GenericInternal\CacheException
         * @throws Exceptions\GenericInternal\DatabaseException
         * @throws Exceptions\GenericInternal\DisplayPictureException
         * @throws Exceptions\GenericInternal\InvalidSearchMethodException
         * @throws Exceptions\Internal\NoPasswordAuthenticationAvailableException
         * @throws Exceptions\Standard\Authentication\AuthenticationNotApplicableException
         * @throws Exceptions\Standard\Authentication\IncorrectTwoFactorAuthenticationCodeException
         * @throws Exceptions\Standard\Authentication\PrivateAccessTokenRequiredException
         * @throws Exceptions\Standard\Authentication\SessionNotFoundException
         * @throws Exceptions\Standard\Authentication\TwoFactorAuthenticationRequiredException
         * @throws Exceptions\Standard\Network\DocumentNotFoundException
         * @throws IncorrectLoginCredentialsException
         * @throws InternalServerException
         * @throws InvalidPasswordException
         * @throws PeerNotFoundException
         */
        public function authenticateUser(string $username, string $password, ?string $otp=null): bool
        {
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
         * @param string $username
         * @param string $password
         * @param string $first_name
         * @param string|null $last_name
         * @return Peer
         * @throws AlreadyAuthenticatedException
         * @throws Exceptions\GenericInternal\CacheException
         * @throws Exceptions\GenericInternal\DatabaseException
         * @throws Exceptions\GenericInternal\DisplayPictureException
         * @throws Exceptions\GenericInternal\InvalidSearchMethodException
         * @throws Exceptions\Standard\Network\DocumentNotFoundException
         * @throws Exceptions\Standard\Validation\InvalidFirstNameException
         * @throws Exceptions\Standard\Validation\InvalidLastNameException
         * @throws Exceptions\Standard\Validation\InvalidUsernameException
         * @throws Exceptions\Standard\Validation\UsernameAlreadyExistsException
         * @throws InternalServerException
         * @throws InvalidPasswordException
         * @throws PeerNotFoundException
         */
        public function registerUser(string $username, string $password, string $first_name, ?string $last_name=null): Peer
        {
            if($this->active_session->Authenticated)
                throw new AlreadyAuthenticatedException("You are already authenticated to the network");

            // Validate the password and throw the proper exception
            Validate::password($password, true);

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
         * Gets Markdown Document that contains information of the terms of service of the network
         *
         * @return HelpDocument
         */
        public function getTermsOfService(): HelpDocument
        {
            $file_path = $this->socialvoidLib->getDataStorageConfiguration()['LegalDocumentsLocation'] . DIRECTORY_SEPARATOR . 'terms_of_service.md';
            $local_path = __DIR__ . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . 'terms_of_service.md';
            if(file_exists($file_path) == false)
                copy($local_path, $file_path);

            return HelpDocument::fromMarkdownDocument(file_get_contents($file_path));
        }

        /**
         * Gets Markdown Document that contains information of the privacy policy of the network
         *
         * @return HelpDocument
         */
        public function getPrivacyPolicy(): HelpDocument
        {
            $file_path = $this->socialvoidLib->getDataStorageConfiguration()['LegalDocumentsLocation'] . DIRECTORY_SEPARATOR . 'privacy_policy.md';
            $local_path = __DIR__ . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . 'privacy_policy.md';
            if(file_exists($file_path) == false)
                copy($local_path, $file_path);

            return HelpDocument::fromMarkdownDocument(file_get_contents($file_path));
        }

        /**
         * Gets a Markdown Document that contains information about the community guidelines of the network
         *
         * @return HelpDocument
         */
        public function getCommunityGuidelines(): HelpDocument
        {
            $file_path = $this->socialvoidLib->getDataStorageConfiguration()['LegalDocumentsLocation'] . DIRECTORY_SEPARATOR . 'community_guidelines.md';
            $local_path = __DIR__ . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . 'community_guidelines.md';
            if(file_exists($file_path) == false)
                copy($local_path, $file_path);

            return HelpDocument::fromMarkdownDocument(file_get_contents($file_path));
        }

        /**
         * Returns information about the server configuration (public)
         *
         * @return ServerInformation
         */
        public function getServerInformation(): ServerInformation
        {
            $ServerInformation = new ServerInformation();

            $ServerInformation->NetworkName = $this->socialvoidLib->getMainConfiguration()['NetworkName'];
            $ServerInformation->CdnServer = $this->socialvoidLib->getCdnConfiguration()['CdnEndpoint'];
            $ServerInformation->UploadMaxFileSize = $this->socialvoidLib->getCdnConfiguration()['MaxFileUploadSize'];
            $ServerInformation->UnauthorizedSessionTTL = (int)$this->socialvoidLib->getMainConfiguration()['UnauthorizedSessionTTL'];
            $ServerInformation->AuthorizedSessionTTL = (int)$this->socialvoidLib->getMainConfiguration()['AuthorizedSessionTTL'];
            $ServerInformation->RetrieveLikesMaxLimit = (int)$this->socialvoidLib->getMainConfiguration()['RetrieveLikesMaxLimit'];
            $ServerInformation->RetrieveRepostsMaxLimit = (int)$this->socialvoidLib->getMainConfiguration()['RetrieveRepostsMaxLimit'];
            $ServerInformation->RetrieveRepliesMaxLimit = (int)$this->socialvoidLib->getMainConfiguration()['RetrieveRepliesMaxLimit'];
            $ServerInformation->RetrieveQuotesMaxLimit = (int)$this->socialvoidLib->getMainConfiguration()['RetrieveQuotesMaxLimit'];
            $ServerInformation->RetrieveFollowersMaxLimit = (int)$this->socialvoidLib->getMainConfiguration()['RetrieveFollowersMixLimit'];
            $ServerInformation->RetrieveFollowingMaxLimit = (int)$this->socialvoidLib->getMainConfiguration()['RetrieveFollowingMixLimit'];
            $ServerInformation->RetrieveFeedMaxLimit = (int)$this->socialvoidLib->getMainConfiguration()['TimelineChunkSize'];

            return $ServerInformation;
        }

        /**
         * @return ProtocolDefinitions
         */
        public static function getProtocolDefinitions(): ProtocolDefinitions
        {
            $ProtocolDefinitions = new ProtocolDefinitions();

            $ProtocolDefinitions->Version = '1.0';

            $ProtocolDefinitions->ErrorDefinitions = [
                // Authentication
                AlreadyAuthenticatedException::getDefinition(),
                AuthenticationFailureException::getDefinition(),
                AuthenticationNotApplicableException::getDefinition(),
                BadSessionChallengeAnswerException::getDefinition(),
                IncorrectLoginCredentialsException::getDefinition(),
                IncorrectTwoFactorAuthenticationCodeException::getDefinition(),
                NotAuthenticatedException::getDefinition(),
                PrivateAccessTokenRequiredException::getDefinition(),
                SessionExpiredException::getDefinition(),
                TwoFactorAuthenticationRequiredException::getDefinition(),

                // Network
                AccessDeniedException::getDefinition(),
                AlreadyRepostedException::getDefinition(),
                BlockedByPeerException::getDefinition(),
                BlockedPeerException::getDefinition(),
                DocumentNotFoundException::getDefinition(),
                FileUploadException::getDefinition(),
                PeerNotFoundException::getDefinition(),
                PostDeletedException::getDefinition(),
                PostNotFoundException::getDefinition(),
                SelfInteractionNotPermittedException::getDefinition(),

                // Server
                DocumentUploadException::getDefinition(),
                InternalServerException::getDefinition(),

                // Validation
                AgreementRequiredException::getDefinition(),
                FileTooLargeException::getDefinition(),
                InvalidAttachmentsException::getDefinition(),
                InvalidBiographyException::getDefinition(),
                InvalidClientNameException::getDefinition(),
                InvalidClientPrivateHashException::getDefinition(),
                InvalidClientPublicHashException::getDefinition(),
                InvalidFileForProfilePictureException::getDefinition(),
                InvalidFileNameException::getDefinition(),
                InvalidFileNameException::getDefinition(),
                InvalidGeoLocationException::getDefinition(),
                InvalidHelpDocumentId::getDefinition(),
                InvalidLastNameException::getDefinition(),
                InvalidPageValueException::getDefinition(),
                InvalidPasswordException::getDefinition(),
                InvalidPeerInputException::getDefinition(),
                InvalidPlatformException::getDefinition(),
                InvalidPostTextException::getDefinition(),
                InvalidSessionIdentificationException::getDefinition(),
                InvalidUrlValueException::getDefinition(),
                InvalidUsernameException::getDefinition(),
                InvalidVersionException::getDefinition(),
                TooManyAttachmentsException::getDefinition(),
                UsernameAlreadyExistsException::getDefinition()
            ];

            return $ProtocolDefinitions;
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
         * @throws Exceptions\GenericInternal\CacheException
         * @throws Exceptions\GenericInternal\DatabaseException
         * @throws Exceptions\Standard\Authentication\SessionNotFoundException
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

        /**
         * @return Account
         */
        public function getAccount(): Account
        {
            return $this->account;
        }
    }
<?php

    namespace SocialvoidRPC\Methods\Account;

    use Exception;
    use KimchiRPC\Exceptions\Server\MissingParameterException;
    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;
    use SocialvoidLib\Abstracts\Flags\PermissionSets;
    use SocialvoidLib\Abstracts\Types\BuiltinTypes;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\DisplayPictureException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Standard\Authentication\BadSessionChallengeAnswerException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionExpiredException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\AccessDeniedException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Security\InsufficientPermissionsException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\Exceptions\Standard\Validation\FileTooLargeException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPublicHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidFileForProfilePictureException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidFileNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPeerInputException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidSessionIdentificationException;
    use SocialvoidLib\Interfaces\StandardMethodInterface;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Standard\MethodDefinition;
    use SocialvoidLib\Objects\Standard\ParameterDefinition;
    use SocialvoidLib\Objects\Standard\SessionIdentification;
    use SocialvoidLib\Objects\Standard\TypeDefinition;
    use SocialvoidRPC\SocialvoidRPC;

    class SetProfilePicture implements MethodInterface, StandardMethodInterface
    {

        /**
         * @inheritDoc
         */
        public function getMethodName(): string
        {
            return "SetProfilePicture";
        }

        /**
         * @inheritDoc
         */
        public function getMethod(): string
        {
            return "account.set_profile_picture";
        }

        /**
         * @inheritDoc
         */
        public function getDescription(): string
        {
            return "Sets the profile picture of the authenticated user from a document ID";
        }

        /**
         * @inheritDoc
         */
        public function getVersion(): string
        {
            return "1.0.0.0";
        }

        /**
         * @inheritDoc
         */
        public static function getStandardNamespace(): string
        {
            return 'account';
        }

        /**
         * @inheritDoc
         */
        public static function getStandardMethodName(): string
        {
            return 'set_profile_picture';
        }

        /**
         * @inheritDoc
         */
        public static function getStandardMethod(): string
        {
            return self::getStandardNamespace() . '.' . self::getStandardMethodName();
        }

        /**
         * @inheritDoc
         */
        public static function getStandardDescription(): string
        {
            return 'Sets a new profile picture to the user from a Document ID';
        }

        /**
         * @inheritDoc
         */
        public static function getStandardPossibleErrorCodes(): array
        {
            return [
                BadSessionChallengeAnswerException::getErrorCode(),
                InternalServerException::getErrorCode(),
                InvalidClientPublicHashException::getErrorCode(),
                InvalidSessionIdentificationException::getErrorCode(),
                NotAuthenticatedException::getErrorCode(),
                SessionExpiredException::getErrorCode(),
                SessionExpiredException::getErrorCode(),
                DocumentNotFoundException::getErrorCode(),
                AccessDeniedException::getErrorCode(),
                InvalidFileForProfilePictureException::getErrorCode(),
                FileTooLargeException::getErrorCode(),
                InsufficientPermissionsException::getErrorCode()
            ];
        }

        /**
         * @inheritDoc
         */
        public static function getReturnTypes(): array
        {
            return [
                new TypeDefinition(BuiltinTypes::Boolean, false)
            ];
        }

        /**
         * @inheritDoc
         */
        public static function getStandardParameters(): array
        {
            return [
                new ParameterDefinition('session_identification', [
                    new TypeDefinition(SessionIdentification::getName()),
                ], true, 'The Session Identification object'),
                new ParameterDefinition('document', [
                    new TypeDefinition(BuiltinTypes::String)
                ], true, 'The ID of the document to retrieve from the server and set as the profile picture, the document must be a type PHOTO')
            ];
        }

        /**
         * @inheritDoc
         */
        public static function getStandardPermissionRequirements(): array
        {
            return [
                PermissionSets::User,
                PermissionSets::Proxy,
                PermissionSets::Bot
            ];
        }

        /**
         * @inheritDoc
         */
        public static function getDefinition(): MethodDefinition
        {
            return new MethodDefinition(
                self::getStandardNamespace(),
                self::getStandardMethodName(),
                self::getStandardMethod(),
                self::getStandardDescription(),
                self::getStandardPermissionRequirements(),
                self::getStandardParameters(),
                self::getStandardPossibleErrorCodes(),
                self::getReturnTypes()
            );
        }

        /**
         * @param Request $request
         * @throws InvalidPeerInputException
         * @throws InvalidSessionIdentificationException
         * @throws MissingParameterException
         * @noinspection DuplicatedCode
         */
        private function checkParameters(Request $request)
        {
            if(isset($request->Parameters["session_identification"]) == false)
                throw new MissingParameterException("Missing parameter 'session_identification'");
            if(gettype($request->Parameters["session_identification"]) !== "array")
                throw new InvalidSessionIdentificationException("The parameter 'session_identification' is not a object");

            if(isset($request->Parameters["document"]) == false)
                throw new InvalidPeerInputException("Missing parameter 'document'");
            if(gettype($request->Parameters["document"]) !== "string")
                throw new InvalidPeerInputException("The parameter 'document' is not a string");

            $parsed_id = explode('-', $request->Parameters['document']);

            // Validate the document ID
            //if(count($parsed_id) !== 2 ||  strlen($parsed_id[0]) !== 64 || strlen($parsed_id[1]) !== 8)
            //    throw new DocumentNotFoundException('The requested document was not found in the network');
        }

        /**
         * @param Request $request
         * @return Response
         * @throws BadSessionChallengeAnswerException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws InvalidSessionIdentificationException
         * @throws MissingParameterException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         * @throws InvalidFileNameException
         * @noinspection DuplicatedCode
         */
        public function execute(Request $request): Response
        {
            $this->checkParameters($request);

            $SessionIdentification = SessionIdentification::fromArray($request->Parameters["session_identification"]);
            $SessionIdentification->validate();

            // Wake the worker up
            SocialvoidRPC::processWakeup();

            // Start the authentication
            $NetworkSession = new NetworkSession(SocialvoidRPC::$SocialvoidLib);

            try
            {
                $NetworkSession->loadSession($SessionIdentification);
            }
            catch(Exception $e)
            {
                // Allow standard errors
                if(Validate::isStandardError($e->getCode()))
                    throw $e;

                // If anything else, suppress the error.
                throw new InternalServerException('There was an unexpected error while trying to loading your session', $e);
            }

            try
            {
                $NetworkSession->getAccount()->setProfilePicture($request->Parameters['document']);
            }
            catch(Exception $e)
            {
                // Allow standard errors
                if(Validate::isStandardError($e->getCode()))
                    throw $e;

                // If anything else, suppress the error.
                throw new InternalServerException('There was an unexpected error while trying to set a profile picture', $e);
            }

            $Response = Response::fromRequest($request);
            $Response->ResultData = true;

            return $Response;
        }
    }
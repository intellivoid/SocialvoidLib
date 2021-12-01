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
    use SocialvoidLib\Exceptions\Standard\Authentication\BadSessionChallengeAnswerException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionExpiredException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPublicHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidSessionIdentificationException;
    use SocialvoidLib\Interfaces\StandardMethodInterface;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Standard\MethodDefinition;
    use SocialvoidLib\Objects\Standard\ParameterDefinition;
    use SocialvoidLib\Objects\Standard\SessionIdentification;
    use SocialvoidLib\Objects\Standard\TypeDefinition;
    use SocialvoidRPC\SocialvoidRPC;

    class DeleteProfilePicture implements MethodInterface, StandardMethodInterface
    {

        /**
         * @inheritDoc
         */
        public function getMethodName(): string
        {
            return "DeleteProfilePicture";
        }

        /**
         * @inheritDoc
         */
        public function getMethod(): string
        {
            return "account.delete_profile_picture";
        }

        /**
         * @inheritDoc
         */
        public function getDescription(): string
        {
            return "Deletes the current profile picture of the authenticated user";
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
            return 'delete_profile_picture';
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
            return 'Deletes the current profile picture and reverts back to the default profile picture generated by the server.';
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
                SessionExpiredException::getErrorCode()
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
                    new TypeDefinition(SessionIdentification::getName())
                ])
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
        }

        /**
         * @param Request $request
         * @return Response
         * @throws BadSessionChallengeAnswerException
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidSessionIdentificationException
         * @throws MissingParameterException
         * @throws NotAuthenticatedException
         * @throws SessionNotFoundException
         */
        public function execute(Request $request): Response
        {
            $this->checkParameters($request);

            $SessionIdentification = SessionIdentification::fromArray($request->Parameters["session_identification"]);
            $SessionIdentification->validate();

            // Wake the worker up
            SocialvoidRPC::processWakeup();

            try
            {
                $NetworkSession = new NetworkSession(SocialvoidRPC::$SocialvoidLib);
                $NetworkSession->getAccount()->deleteProfilePicture();
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
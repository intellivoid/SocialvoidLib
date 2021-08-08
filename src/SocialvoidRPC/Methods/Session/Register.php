<?php

    namespace SocialvoidRPC\Methods\Session;

    use Exception;
    use KimchiRPC\Exceptions\Server\MissingParameterException;
    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Standard\Authentication\AlreadyAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Authentication\BadSessionChallengeAnswerException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionExpiredException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPublicHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidFirstNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidLastNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPasswordException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidSessionIdentificationException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidUsernameException;
    use SocialvoidLib\Exceptions\Standard\Validation\UsernameAlreadyExistsException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Standard\SessionIdentification;
    use SocialvoidRPC\SocialvoidRPC;

    class Register implements MethodInterface
    {

        /**
         * @inheritDoc
         */
        public function getMethodName(): string
        {
            return "Register";
        }

        /**
         * @inheritDoc
         */
        public function getMethod(): string
        {
            return "session.register";
        }

        /**
         * @inheritDoc
         */
        public function getDescription(): string
        {
            return "Registers a new peer into the network";
        }

        /**
         * @inheritDoc
         */
        public function getVersion(): string
        {
            return "1.0.0.0";
        }

        private function checkParameters(Request $request)
        {
            if(isset($request->Parameters["session_identification"]) == false)
                throw new MissingParameterException("Missing parameter 'session_identification'");
            if(gettype($request->Parameters["session_identification"]) !== "array")
                throw new InvalidSessionIdentificationException("The parameter 'session_identification' is not a object");

            if(isset($request->Parameters["username"]) == false)
                throw new MissingParameterException("Missing parameter 'username'");
            if(gettype($request->Parameters["username"]) !== "string")
                throw new InvalidUsernameException("The 'username' parameter must be a string", $request->Parameters["username"]);

            if(isset($request->Parameters["password"]) == false)
                throw new MissingParameterException("Missing parameter 'password'");
            if(gettype($request->Parameters["password"]) !== "string")
                throw new InvalidPasswordException("The 'password' parameter must be a string", $request->Parameters["password"]);

            if(isset($request->Parameters["first_name"]) == false)
                throw new MissingParameterException("Missing parameter 'first_name'");
            if(gettype($request->Parameters["first_name"]) !== "string")
                throw new InvalidFirstNameException("The 'first_name' parameter must be a string", $request->Parameters["first_name"]);

            if(isset($request->Parameters["last_name"]))
            {
                if(gettype($request->Parameters["last_name"]) !== "string")
                    throw new InvalidLastNameException("The 'last_name' parameter must be a string", $request->Parameters["last_name"]);
            }
        }

        /**
         * @param Request $request
         * @return Response
         * @throws AlreadyAuthenticatedException
         * @throws BadSessionChallengeAnswerException
         * @throws CacheException
         * @throws DatabaseException
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidFirstNameException
         * @throws InvalidLastNameException
         * @throws InvalidPasswordException
         * @throws InvalidSearchMethodException
         * @throws InvalidSessionIdentificationException
         * @throws InvalidUsernameException
         * @throws MissingParameterException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         * @throws UsernameAlreadyExistsException
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
                $RegisteredPeer = $NetworkSession->registerUser(
                    $SessionIdentification,
                    $request->Parameters["username"],
                    $request->Parameters["password"],
                    $request->Parameters["first_name"],
                    ($request->Parameters["last_name"] ?? null),
                );
            }
            catch (Exception $e)
            {
                // Allow standard errors
                if(Validate::isStandardError($e->getCode()))
                    throw $e;

                // If anything else, suppress the error.
                throw new InternalServerException("There was an unexpected error while tyring to register the peer to the network", $e);
            }

            $Response = Response::fromRequest($request);
            $Response->ResultData = $RegisteredPeer->toArray();
            return $Response;
        }
    }
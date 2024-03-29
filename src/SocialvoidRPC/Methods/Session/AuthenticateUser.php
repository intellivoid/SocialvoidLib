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
    use SocialvoidLib\Exceptions\GenericInternal\DisplayPictureException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Standard\Authentication\BadSessionChallengeAnswerException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionExpiredException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPublicHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPasswordException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidSessionIdentificationException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidUsernameException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Standard\SessionIdentification;
    use SocialvoidRPC\SocialvoidRPC;

    /**
     * Class AuthenticateUser
     * @package SocialvoidRPC\Methods\Session
     */
    class AuthenticateUser implements MethodInterface
    {
        /**
         * @return string
         */
        public function getMethodName(): string
        {
            return "AuthenticateUser";
        }

        /**
         * @return string
         */
        public function getMethod(): string
        {
            return "session.authenticate_user";
        }

        /**
         * @return string
         */
        public function getDescription(): string
        {
            return "Authenticates a user to the requested session";
        }

        /**
         * @return string
         */
        public function getVersion(): string
        {
            return "1.0.0.0";
        }

        /**
         * Checks the parameters of the server
         *
         * @param Request $request
         * @throws InvalidPasswordException
         * @throws InvalidSessionIdentificationException
         * @throws InvalidUsernameException
         * @throws MissingParameterException
         * @noinspection DuplicatedCode
         */
        private function checkParameters(Request $request)
        {
            /** @noinspection DuplicatedCode */
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
         * @throws InvalidPasswordException
         * @throws InvalidSearchMethodException
         * @throws InvalidSessionIdentificationException
         * @throws InvalidUsernameException
         * @throws MissingParameterException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
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

            $otp_code = null;

            if(
                isset($request->Parameters["otp"]) &&
                gettype($request->Parameters["otp"]) == "string" &&
                strlen($request->Parameters["otp"]) > 0 &&
                strlen($request->Parameters["otp"] < 64)
            )
                $otp_code = $request->Parameters["otp"];

            try
            {
                $NetworkSession->authenticateUser(
                    $request->Parameters["username"],
                    $request->Parameters["password"],
                    $otp_code // The parameter is null if it isn't included
                );
            }
            catch(Exception $e)
            {
                // Allow standard errors
                if(Validate::isStandardError($e->getCode()))
                    throw $e;

                // If anything else, suppress the error.
                throw new InternalServerException("There was an unexpected error while tyring to establish your session", $e);
            }

            $Response = Response::fromRequest($request);
            $Response->ResultData = true;
            return $Response;
        }
    }
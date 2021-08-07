<?php


    namespace SocialvoidRPC\Methods\Session;

    use Exception;
    use KimchiRPC\Exceptions\Server\MissingParameterException;
    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Standard\Authentication\BadSessionChallengeAnswerException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionExpiredException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPublicHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidSessionIdentificationException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Standard\Session;
    use SocialvoidLib\Objects\Standard\SessionIdentification;
    use SocialvoidRPC\SocialvoidRPC;

    /**
     * Class GetSession
     * @package SocialvoidRPC\Methods\Session
     */
    class GetSession implements MethodInterface
    {
        /**
         * @return string
         */
        public function getMethodName(): string
        {
            return "GetSession";
        }

        /**
         * @return string
         */
        public function getMethod(): string
        {
            return "session.get";
        }

        /**
         * @return string
         */
        public function getDescription(): string
        {
            return "Returns the session object detailing information about the session state";
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
         * @throws InvalidSessionIdentificationException
         * @throws MissingParameterException
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
         * @throws InternalServerException
         * @throws InvalidSessionIdentificationException
         * @throws MissingParameterException
         * @throws DatabaseException !may
         * @throws InvalidSearchMethodException !may
         * @throws BadSessionChallengeAnswerException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         * @throws InvalidClientPublicHashException
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
                throw new InternalServerException("There was an unexpected error while tyring to logout", $e);
            }

            $Response = Response::fromRequest($request);
            $Response->ResultData = Session::fromActiveSession($NetworkSession->getActiveSession())->toArray();
            return $Response;
        }
    }
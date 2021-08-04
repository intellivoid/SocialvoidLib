<?php


    namespace SocialvoidRPC\Methods\Session;

    use KimchiRPC\Exceptions\Server\MissingParameterException;
    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPrivateHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPublicHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPlatformException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidVersionException;
    use SocialvoidLib\InputTypes\SessionClient;
    use SocialvoidLib\NetworkSession;
    use SocialvoidRPC\SocialvoidRPC;

    /**
     * Class CreateSession
     * @package SocialvoidRPC\Methods\Session
     */
    class CreateSession implements MethodInterface
    {
        /**
         * @return string
         */
        public function getMethodName(): string
        {
            return "CreateSession";
        }

        /**
         * @return string
         */
        public function getMethod(): string
        {
            return "session.create";
        }

        /**
         * @return string
         */
        public function getDescription(): string
        {
            return
                "Establishes a new session, this session expires in 10 minutes, authenticating will increase the " .
                "expiration time to 72 hours that resets every time the session is used";
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
         * @throws MissingParameterException
         */
        private function checkParameters(Request $request)
        {
            if(isset($request->Parameters["public_hash"]) == false)
                throw new MissingParameterException("Missing parameter 'public_hash'");

            if(isset($request->Parameters["private_hash"]) == false)
                throw new MissingParameterException("Missing parameter 'private_hash'");

            if(isset($request->Parameters["platform"]) == false)
                throw new MissingParameterException("Missing parameter 'platform'");

            if(isset($request->Parameters["name"]) == false)
                throw new MissingParameterException("Missing parameter 'name'");

            if(isset($request->Parameters["version"]) == false)
                throw new MissingParameterException("Missing parameter 'version'");
        }

        /**
         * @param Request $request
         * @return Response
         * @throws InternalServerException
         * @throws MissingParameterException
         * @throws InvalidClientNameException
         * @throws InvalidClientPrivateHashException
         * @throws InvalidClientPublicHashException
         * @throws InvalidPlatformException
         * @throws InvalidVersionException
         */
        public function execute(Request $request): Response
        {
            $this->checkParameters($request);

            $SessionClient = SessionClient::fromArray($request->Parameters);
            SocialvoidRPC::processWakeup(); // Wake the worker up

            $NetworkSession = new NetworkSession(SocialvoidRPC::$SocialvoidLib);
            try
            {
                $SessionEstablished = $NetworkSession->createSession($SessionClient, $request->ClientIP);
            }
            catch(\Exception $e)
            {
                // Allow standard errors
                if(Validate::isStandardError($e->getCode()))
                    throw $e;

                // If anything else, suppress the error.
                throw new InternalServerException("There was an unexpected error while tyring to establish your session", $e);
            }

            $Response = Response::fromRequest($request);
            $Response->ResultData = $SessionEstablished->toArray();
            return $Response;
        }
    }
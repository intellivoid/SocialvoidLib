<?php


    namespace SocialvoidRPC\Methods\Session;

    use KimchiRPC\Exceptions\Server\MissingParameterException;
    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
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
            return "Creates a new session for your client";
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
         * @throws MissingParameterException
         * @throws InternalServerException
         */
        public function execute(Request $request): Response
        {
            $this->checkParameters($request);

            $SessionClient = SessionClient::fromArray($request->Parameters);
            $NetworkSession = new NetworkSession(SocialvoidRPC::getSocialvoidLib());
            $SessionEstablished = $NetworkSession->createSession($SessionClient, $request->ClientIP);

            $Response = Response::fromRequest($request);
            $Response->ResultData = $SessionEstablished->toArray();
            return $Response;
        }
    }
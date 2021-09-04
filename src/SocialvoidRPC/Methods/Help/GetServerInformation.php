<?php

    namespace SocialvoidRPC\Methods\Help;

    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;
    use SocialvoidLib\NetworkSession;
    use SocialvoidRPC\SocialvoidRPC;

    class GetServerInformation implements MethodInterface
    {

        /**
         * @inheritDoc
         */
        public function getMethodName(): string
        {
            return 'GetServerInformation';
        }

        /**
         * @inheritDoc
         */
        public function getMethod(): string
        {
            return 'help.get_server_information';
        }

        /**
         * @inheritDoc
         */
        public function getDescription(): string
        {
            return 'Returns basic information about the server attributes';
        }

        /**
         * @inheritDoc
         */
        public function getVersion(): string
        {
            return '1.0.0.0';
        }

        /**
         * @inheritDoc
         */
        public function execute(Request $request): Response
        {
            $NetworkSession = new NetworkSession(SocialvoidRPC::$SocialvoidLib);
            $Response = Response::fromRequest($request);
            $Response->ResultData = $NetworkSession->getServerInformation()->toArray();
            return $Response;
        }
    }
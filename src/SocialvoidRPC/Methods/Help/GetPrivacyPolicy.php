<?php

    namespace SocialvoidRPC\Methods\Help;

    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;
    use SocialvoidLib\NetworkSession;
    use SocialvoidRPC\SocialvoidRPC;

    class GetPrivacyPolicy implements MethodInterface
    {

        /**
         * @inheritDoc
         */
        public function getMethodName(): string
        {
            return "GetPrivacyPolicy";
        }

        /**
         * @inheritDoc
         */
        public function getMethod(): string
        {
            return "help.get_privacy_policy";
        }

        /**
         * @inheritDoc
         */
        public function getDescription(): string
        {
            return "Returns the contents of a Markdown document that states the privacy policy of the network";
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
        public function execute(Request $request): Response
        {
            $NetworkSession = new NetworkSession(SocialvoidRPC::$SocialvoidLib);
            $Response = Response::fromRequest($request);
            $Response->ResultData = $NetworkSession->getPrivacyPolicy()->toArray();
            return $Response;
        }
    }
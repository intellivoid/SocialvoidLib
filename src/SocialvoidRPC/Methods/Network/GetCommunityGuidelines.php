<?php

    namespace SocialvoidRPC\Methods\Network;

    use KimchiRPC\Exceptions\RequestException;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;
    use SocialvoidLib\NetworkSession;
    use SocialvoidRPC\SocialvoidRPC;

    class GetCommunityGuidelines implements \KimchiRPC\Interfaces\MethodInterface
    {

        /**
         * @inheritDoc
         */
        public function getMethodName(): string
        {
            return "GetCommunityGuidelines";
        }

        /**
         * @inheritDoc
         */
        public function getMethod(): string
        {
            return "network.get_community_guidelines";
        }

        /**
         * @inheritDoc
         */
        public function getDescription(): string
        {
            return "Returns the contents of a Markdown document that states the community guidelines of the network";
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
            $Response->ResultData = $NetworkSession->getCommunityGuidelines();

            return $Response;
        }
    }
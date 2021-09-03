<?php

    namespace SocialvoidRPC\Methods\Help;

    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;
    use SocialvoidLib\Abstracts\Modes\Standard\ParseMode;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\NetworkSession;
    use SocialvoidRPC\SocialvoidRPC;

    class GetCommunityGuidelines implements MethodInterface
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
            return "help.get_community_guidelines";
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
            $Response->ResultData = $NetworkSession->getCommunityGuidelines()->toArray();
            return $Response;
        }
    }
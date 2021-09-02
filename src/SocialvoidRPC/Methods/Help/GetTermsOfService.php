<?php

    namespace SocialvoidRPC\Methods\Help;

    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;
    use SocialvoidLib\Abstracts\Modes\Standard\ParseMode;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\NetworkSession;
    use SocialvoidRPC\SocialvoidRPC;

    class GetTermsOfService implements MethodInterface
    {

        /**
         * @inheritDoc
         */
        public function getMethodName(): string
        {
            return "GetTermsOfService";
        }

        /**
         * @inheritDoc
         */
        public function getMethod(): string
        {
            return "help.get_terms_of_service";
        }

        /**
         * @inheritDoc
         */
        public function getDescription(): string
        {
            return "Returns the contents of a Markdown document that states the Terms of Service of the network";
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
            $Content = $NetworkSession->getTermsOfService();
            $Response = Response::fromRequest($request);

            $Entities = Utilities::extractTextEntities($Content, ParseMode::Markdown);
            $EntitiesArray = [];
            foreach($Entities as $entity)
                $EntitiesArray[] = $entity->toArray();

            $Response->ResultData = [
                'text' => Utilities::extractTextWithoutEntities($Content, ParseMode::Markdown),
                'entities' => $EntitiesArray
            ];

            return $Response;
        }
    }
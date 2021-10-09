<?php

    namespace SocialvoidRPC\Methods\Timeline;

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
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPostTextException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidSessionIdentificationException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Standard\SessionIdentification;
    use SocialvoidRPC\SocialvoidRPC;

    class Quote implements MethodInterface
    {
        /**
         * @inheritDoc
         */
        public function getMethodName(): string
        {
            return 'Quote';
        }

        /**
         * @inheritDoc
         */
        public function getMethod(): string
        {
            return 'timeline.quote';
        }

        /**
         * @inheritDoc
         */
        public function getDescription(): string
        {
            return 'Composes a new post by quoting an existing post';
        }

        /**
         * @inheritDoc
         */
        public function getVersion(): string
        {
            return '1.0.0.0';
        }

        /**
         * Checks the parameters for invalid data or missing parameters
         *
         * @param Request $request
         * @throws InvalidPostTextException
         * @throws InvalidSessionIdentificationException
         * @throws MissingParameterException
         * @noinspection DuplicatedCode
         */
        private function checkParameters(Request $request)
        {
            if(isset($request->Parameters["session_identification"]) == false)
                throw new MissingParameterException("Missing parameter 'session_identification'");
            if(gettype($request->Parameters["session_identification"]) !== "array")
                throw new InvalidSessionIdentificationException("The parameter 'session_identification' is not a object");

            if(isset($request->Parameters['post']) == false)
                throw new MissingParameterException('Missing parameter \'post\'');
            if(gettype($request->Parameters['post']) !== 'string')
                throw new InvalidPostTextException('The parameter \'post\' must be a string');

            if(isset($request->Parameters['text']) == false)
                throw new MissingParameterException('Missing parameter \'text\'');
            if(gettype($request->Parameters['text']) !== 'string')
                throw new InvalidPostTextException('The parameter \'text\' must be a string');
        }

        /**
         * @param Request $request
         * @return Response
         * @throws BadSessionChallengeAnswerException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidPostTextException
         * @throws InvalidSearchMethodException
         * @throws InvalidSessionIdentificationException
         * @throws MissingParameterException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         * @throws DisplayPictureException
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

            try
            {
                $ComposedPost = $NetworkSession->getTimeline()->quote($request->Parameters['post'], $request->Parameters['text']);
                $StandardObject = $NetworkSession->getTimeline()->getStandardPost($ComposedPost->PublicID);
            }
            catch(Exception $e)
            {
                // Allow standard errors
                if(Validate::isStandardError($e->getCode()))
                    throw $e;

                // If anything else, suppress the error.
                throw new InternalServerException('There was an unexpected error while trying to publish your post', $e);
            }

            $Response = Response::fromRequest($request);
            $Response->ResultData = $StandardObject->toArray();

            return $Response;
        }
    }
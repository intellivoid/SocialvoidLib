<?php

    namespace SocialvoidRPC\Methods\Timeline;

    use Exception;
    use KimchiRPC\Exceptions\Server\MissingParameterException;
    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\GenericInternal\BackgroundWorkerNotEnabledException;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSlaveHashException;
    use SocialvoidLib\Exceptions\Internal\FollowerDataNotFound;
    use SocialvoidLib\Exceptions\Internal\UserTimelineNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Authentication\BadSessionChallengeAnswerException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionExpiredException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PostNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPublicHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPostTextException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidSessionIdentificationException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Standard\Peer;
    use SocialvoidLib\Objects\Standard\Post;
    use SocialvoidLib\Objects\Standard\SessionIdentification;
    use SocialvoidRPC\SocialvoidRPC;
    use udp2\Exceptions\AvatarGeneratorException;
    use udp2\Exceptions\AvatarNotFoundException;
    use udp2\Exceptions\ImageTooSmallException;
    use udp2\Exceptions\UnsupportedAvatarGeneratorException;
    use Zimage\Exceptions\CannotGetOriginalImageException;
    use Zimage\Exceptions\FileNotFoundException;
    use Zimage\Exceptions\InvalidZimageFileException;
    use Zimage\Exceptions\SizeNotSetException;
    use Zimage\Exceptions\UnsupportedImageTypeException;

    class ComposePost implements MethodInterface
    {
        /**
         * @inheritDoc
         */
        public function getMethodName(): string
        {
            return 'ComposePost';
        }

        /**
         * @inheritDoc
         */
        public function getMethod(): string
        {
            return 'timeline.compose_post';
        }

        /**
         * @inheritDoc
         */
        public function getDescription(): string
        {
            return 'Composes a new post to push to the timeline';
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
         */
        private function checkParameters(Request $request)
        {
            if(isset($request->Parameters["session_identification"]) == false)
                throw new MissingParameterException("Missing parameter 'session_identification'");
            if(gettype($request->Parameters["session_identification"]) !== "array")
                throw new InvalidSessionIdentificationException("The parameter 'session_identification' is not a object");

            if(isset($request->Parameters['text']) == false)
                throw new MissingParameterException('Missing parameter \'text\'');
            if(gettype($request->Parameters['text']) !== 'string')
                throw new InvalidPostTextException('The parameter \'text\' must be a string');
        }

        /**
         * @param Request $request
         * @return Response
         * @throws AvatarGeneratorException
         * @throws AvatarNotFoundException
         * @throws BadSessionChallengeAnswerException
         * @throws CacheException
         * @throws CannotGetOriginalImageException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws FileNotFoundException
         * @throws ImageTooSmallException
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidPostTextException
         * @throws InvalidSearchMethodException
         * @throws InvalidSessionIdentificationException
         * @throws InvalidSlaveHashException
         * @throws InvalidZimageFileException
         * @throws MissingParameterException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         * @throws SizeNotSetException
         * @throws UnsupportedAvatarGeneratorException
         * @throws UnsupportedImageTypeException
         * @throws BackgroundWorkerNotEnabledException
         * @throws FollowerDataNotFound
         * @throws UserTimelineNotFoundException
         * @throws PostNotFoundException
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
                $ComposedPost = $NetworkSession->getTimeline()->composePost($request->Parameters['text']);
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
            $StandardObject = Post::fromPost($ComposedPost);
            $StandardObject->Peer = Peer::fromUser($NetworkSession->getAuthenticatedUser());
            $Response->ResultData = $StandardObject->toArray();

            return $Response;
        }
    }
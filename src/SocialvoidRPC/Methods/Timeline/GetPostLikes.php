<?php

    namespace SocialvoidRPC\Methods\Timeline;

    use Exception;
    use KimchiRPC\Exceptions\Server\InvalidParametersException;
    use KimchiRPC\Exceptions\Server\MissingParameterException;
    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSlaveHashException;
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
    use SocialvoidLib\Objects\Standard\Peer;
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

    class GetPostLikes implements MethodInterface
    {

        /**
         * @inheritDoc
         */
        public function getMethodName(): string
        {
            return 'GetPostLikes';
        }

        /**
         * @inheritDoc
         */
        public function getMethod(): string
        {
            return 'timeline.get_post_likes';
        }

        /**
         * @inheritDoc
         */
        public function getDescription(): string
        {
            return 'Returns the peers that liked the requested post';
        }

        /**
         * @inheritDoc
         */
        public function getVersion(): string
        {
            return '1.0.0.0';
        }

        /**
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

            if(isset($request->Parameters['post_id']) == false)
                throw new MissingParameterException('Missing parameter \'text\'');
            if(gettype($request->Parameters['post_id']) !== 'string')
                throw new InvalidPostTextException('The parameter \'text\' must be a string');

            if(isset($request->Parameters['offset']))
            {
                $request->Parameters['offset'] = 0;
            }

            if(isset($request->Parameters['limit']))
            {
                $request->Parameters['limit'] = 25;
            }
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
         * @throws InvalidZimageFileException
         * @throws MissingParameterException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         * @throws SizeNotSetException
         * @throws UnsupportedAvatarGeneratorException
         * @throws UnsupportedImageTypeException
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
                $Peers = $NetworkSession->getTimeline()->getPostLikes(
                    $request->Parameters['post_id'], (int)$request->Parameters['offset'], (int)$request->Parameters['limit']
                );
            }
            catch(Exception $e)
            {
                // Allow standard errors
                if(Validate::isStandardError($e->getCode()))
                    throw $e;

                // If anything else, suppress the error.
                throw new InternalServerException('There was an unexpected error while trying to get the requested post', $e);
            }

            $Response = Response::fromRequest($request);
            $Response->ResultData = [];
            foreach($Peers as $peer)
                $Response->ResultData[] = Peer::fromUser($peer)->toArray();

            return $Response;
        }
    }
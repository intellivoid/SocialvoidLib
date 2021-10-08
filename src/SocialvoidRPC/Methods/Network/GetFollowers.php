<?php

    namespace SocialvoidRPC\Methods\Network;

    use Exception;
    use KimchiRPC\Exceptions\Server\MissingParameterException;
    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Standard\Authentication\BadSessionChallengeAnswerException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionExpiredException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPublicHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPeerInputException;
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

    class GetFollowers implements MethodInterface
    {

        /**
         * @inheritDoc
         */
        public function getMethodName(): string
        {
            return 'GetFollowers';
        }

        /**
         * @inheritDoc
         */
        public function getMethod(): string
        {
            return 'network.get_followers';
        }

        /**
         * @inheritDoc
         */
        public function getDescription(): string
        {
            return 'Returns an array of peers that follows the given peer';
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
         * @throws InvalidSessionIdentificationException
         * @throws MissingParameterException
         * @throws InvalidPeerInputException
         * @noinspection DuplicatedCode
         */
        private function checkParameters(Request $request)
        {
            if(isset($request->Parameters['session_identification']) == false)
                throw new MissingParameterException('Missing parameter \'session_identification\'');
            if(gettype($request->Parameters['session_identification']) !== 'array')
                throw new InvalidSessionIdentificationException('The parameter \'session_identification\' is not a object');

            if(isset($request->Parameters['peer']))
            {
                if(gettype($request->Parameters['peer']) !== 'string')
                    throw new InvalidPeerInputException('The parameter \'peer\' is not a string');
            }

            if(isset($request->Parameters['offset']))
            {
                $request->Parameters['offset'] = 0;
            }

            if(isset($request->Parameters['limit']))
            {
                $request->Parameters['limit'] = 100;
            }
        }

        /**
         * @param Request $request
         * @return Response
         * @throws InternalServerException
         * @throws InvalidPeerInputException
         * @throws InvalidSessionIdentificationException
         * @throws MissingParameterException
         * @throws CacheException
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws BadSessionChallengeAnswerException
         * @throws NotAuthenticatedException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         * @throws DocumentNotFoundException
         * @throws PeerNotFoundException
         * @throws InvalidClientPublicHashException
         * @throws CannotGetOriginalImageException
         * @throws FileNotFoundException
         * @throws InvalidZimageFileException
         * @throws SizeNotSetException
         * @throws UnsupportedImageTypeException
         * @throws AvatarGeneratorException
         * @throws AvatarNotFoundException
         * @throws ImageTooSmallException
         * @throws UnsupportedAvatarGeneratorException
         * @noinspection DuplicatedCode
         */
        public function execute(Request $request): Response
        {
            $this->checkParameters($request);

            $SessionIdentification = SessionIdentification::fromArray($request->Parameters['session_identification']);
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
                $requested_peer = $request->Parameters['peer'] ?? $NetworkSession->getAuthenticatedUser()->PublicID;

                $followers = $NetworkSession->getUsers()->getFollowers($requested_peer, $request->Parameters['limit'], $request->Parameters['offset']);
                $followers_std = [];

                foreach($followers as $follower)
                    $followers_std[] = Peer::fromUser($follower)->toArray();

                $Response = Response::fromRequest($request);
                $Response->ResultData = $followers_std;

                return $Response;
            }
            catch(Exception $e)
            {
                // Allow standard errors
                if(Validate::isStandardError($e->getCode()))
                    throw $e;

                // If anything else, suppress the error.There was an unexpected error while processing the requested peer
                throw new InternalServerException('There was an unexpected error while processing the requested peer', $e);
            }
        }
    }
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
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPageValueException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPeerInputException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidSessionIdentificationException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Standard\Peer;
    use SocialvoidLib\Objects\Standard\SessionIdentification;
    use SocialvoidRPC\SocialvoidRPC;

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
         * @throws InvalidPageValueException
         * @throws InvalidPeerInputException
         * @throws InvalidSessionIdentificationException
         * @throws MissingParameterException
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

            if(isset($request->Parameters['page']) == false)
            {
                $request->Parameters['page'] = 1;
            }
            else
            {
                if(gettype($request->Parameters['page']) !== 'integer')
                    throw new InvalidPageValueException('The parameter \'page\' must be a integer');
            }
        }


        /**
         * @param Request $request
         * @return Response
         * @throws BadSessionChallengeAnswerException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidPageValueException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws InvalidSessionIdentificationException
         * @throws MissingParameterException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
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

                $followers = $NetworkSession->getUsers()->getFollowers($requested_peer, $request->Parameters['page']);
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
<?php /** @noinspection DuplicatedCode */

namespace SocialvoidRPC\Methods\Network;

    use Exception;
    use KimchiRPC\Exceptions\RequestException;
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
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPublicHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPeerInputException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidSessionIdentificationException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Standard\Peer;
    use SocialvoidLib\Objects\Standard\SessionIdentification;
    use SocialvoidRPC\SocialvoidRPC;

    class ResolvePeer implements MethodInterface
    {

        /**
         * @inheritDoc
         */
        public function getMethodName(): string
        {
            return "ResolvePeer";
        }

        /**
         * @return string
         */
        public function getMethod(): string
        {
            return "network.resolve_peer";
        }

        /**
         * @return string
         */
        public function getDescription(): string
        {
            return "Resolves a peer from the network";
        }

        /**
         * @return string
         */
        public function getVersion(): string
        {
            return "1.0.0.0";
        }

        /**
         * @param Request $request
         * @throws InvalidSessionIdentificationException
         * @throws MissingParameterException
         * @throws InvalidPeerInputException
         */
        private function checkParameters(Request $request)
        {
            if(isset($request->Parameters["session_identification"]) == false)
                throw new MissingParameterException("Missing parameter 'session_identification'");
            if(gettype($request->Parameters["session_identification"]) !== "array")
                throw new InvalidSessionIdentificationException("The parameter 'session_identification' is not a object");

            if(isset($request->Parameters["peer"]) == false)
                throw new InvalidPeerInputException("Missing parameter 'peer'");
            if(gettype($request->Parameters["peer"]) !== "string")
                throw new InvalidPeerInputException("The parameter 'peer' is not a string");
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
         * @throws PeerNotFoundException
         * @throws InvalidClientPublicHashException
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
                throw new InternalServerException("There was an unexpected error", $e);
            }

            try
            {
                $resolved_peer = $NetworkSession->getUsers()->resolvePeer($SessionIdentification, $request->Parameters["peer"]);
            }
            catch(Exception $e)
            {
                // Allow standard errors
                if(Validate::isStandardError($e->getCode()))
                    throw $e;

                // If anything else, suppress the error.
                throw new InternalServerException("There was an unexpected error", $e);
            }
            
            $Response = Response::fromRequest($request);
            $Response->ResultData = Peer::fromUser($resolved_peer)->toArray();

            return $Response;
        }
    }
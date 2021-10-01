<?php /** @noinspection DuplicatedCode */

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
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSlaveHashException;
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
    use SocialvoidLib\Objects\Standard\Profile;
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
         * @throws BadSessionChallengeAnswerException
         * @throws CacheException
         * @throws DatabaseException
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidPeerInputException
         * @throws InvalidSearchMethodException
         * @throws InvalidSessionIdentificationException
         * @throws InvalidSlaveHashException
         * @throws MissingParameterException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         * @throws DocumentNotFoundException
         * @throws CannotGetOriginalImageException
         * @throws FileNotFoundException
         * @throws InvalidZimageFileException
         * @throws SizeNotSetException
         * @throws UnsupportedImageTypeException
         * @throws AvatarGeneratorException
         * @throws AvatarNotFoundException
         * @throws ImageTooSmallException
         * @throws UnsupportedAvatarGeneratorException
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
                $resolved_peer = $NetworkSession->getUsers()->resolvePeer($request->Parameters["peer"]);
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
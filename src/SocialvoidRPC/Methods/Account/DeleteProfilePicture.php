<?php

    namespace SocialvoidRPC\Methods\Account;

    use Exception;
    use KimchiRPC\Exceptions\Server\MissingParameterException;
    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\Standard\Authentication\BadSessionChallengeAnswerException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPublicHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidSessionIdentificationException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Standard\SessionIdentification;
    use SocialvoidRPC\SocialvoidRPC;

    class DeleteProfilePicture implements MethodInterface
    {

        /**
         * @inheritDoc
         */
        public function getMethodName(): string
        {
            return "DeleteProfilePicture";
        }

        /**
         * @inheritDoc
         */
        public function getMethod(): string
        {
            return "account.delete_profile_picture";
        }

        /**
         * @inheritDoc
         */
        public function getDescription(): string
        {
            return "Deletes the current profile picture of the authenticated user";
        }

        /**
         * @inheritDoc
         */
        public function getVersion(): string
        {
            return "1.0.0.0";
        }

        /**
         * @param Request $request
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
        }

        /**
         * @param Request $request
         * @return Response
         * @throws BadSessionChallengeAnswerException
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidSessionIdentificationException
         * @throws MissingParameterException
         * @throws NotAuthenticatedException
         * @throws SessionNotFoundException
         */
        public function execute(Request $request): Response
        {
            $this->checkParameters($request);

            $SessionIdentification = SessionIdentification::fromArray($request->Parameters["session_identification"]);
            $SessionIdentification->validate();

            // Wake the worker up
            SocialvoidRPC::processWakeup();

            try
            {
                $NetworkSession = new NetworkSession(SocialvoidRPC::$SocialvoidLib);
                $NetworkSession->getAccount()->deleteProfilePicture();
            }
            catch(Exception $e)
            {
                // Allow standard errors
                if(Validate::isStandardError($e->getCode()))
                    throw $e;

                // If anything else, suppress the error.
                throw new InternalServerException('There was an unexpected error while trying to set a profile picture', $e);
            }

            $Response = Response::fromRequest($request);
            $Response->ResultData = true;

            return $Response;
        }
    }
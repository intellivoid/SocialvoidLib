<?php

    namespace SocialvoidRPC\Methods\Captcha;

    use Exception;
    use KimchiRPC\Exceptions\Server\MissingParameterException;
    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\Standard\Security\CaptchaNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidCaptchaIdException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidRPC\SocialvoidRPC;

    class GetCaptcha implements MethodInterface
    {

        /**
         * @inheritDoc
         */
        public function getMethodName(): string
        {
            return 'GetCaptcha';
        }

        /**
         * @inheritDoc
         */
        public function getMethod(): string
        {
            return 'captcha.get';
        }

        /**
         * @inheritDoc
         */
        public function getDescription(): string
        {
            return 'Returns an existing captcha instance';
        }

        /**
         * @inheritDoc
         */
        public function getVersion(): string
        {
            return '1.0.0.0';
        }

        /**
         * Checks the parameters of the server
         *
         * @param Request $request
         * @throws InvalidCaptchaIdException
         * @throws MissingParameterException
         */
        private function checkParameters(Request $request)
        {
            if(isset($request->Parameters['captcha']) == false)
                throw new MissingParameterException("Missing parameter 'captcha'");
            if(gettype($request->Parameters['captcha']) !== 'string')
                throw new InvalidCaptchaIdException('The parameter \'captcha\' is not a string');
        }

        /**
         * @param Request $request
         * @return Response
         * @throws CaptchaNotFoundException
         * @throws DatabaseException
         * @throws InternalServerException
         * @throws InvalidCaptchaIdException
         * @throws MissingParameterException
         */
        public function execute(Request $request): Response
        {
            $this->checkParameters($request);
            $NetworkSession = new NetworkSession(SocialvoidRPC::$SocialvoidLib);

            $Response = Response::fromRequest($request);

            try
            {
                $Response->ResultData = $NetworkSession->getCaptcha()->getCaptcha($request->Parameters['captcha'])->toArray();
            }
            catch(Exception $e)
            {
                // Allow standard errors
                if(Validate::isStandardError($e->getCode()))
                    throw $e;

                // If anything else, suppress the error.
                throw new InternalServerException('There was an unexpected error trying to retrieve the requested captcha instance', $e);
            }

            return $Response;
        }
    }
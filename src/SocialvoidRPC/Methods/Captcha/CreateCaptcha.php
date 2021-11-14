<?php

    namespace SocialvoidRPC\Methods\Captcha;

    use Exception;
    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\Standard\Security\CaptchaNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidRPC\SocialvoidRPC;

    class CreateCaptcha implements MethodInterface
    {

        /**
         * @inheritDoc
         */
        public function getMethodName(): string
        {
            return 'CreateCaptcha';
        }

        /**
         * @inheritDoc
         */
        public function getMethod(): string
        {
            return 'captcha.create';
        }

        /**
         * @inheritDoc
         */
        public function getDescription(): string
        {
            return 'Creates a new captcha instance';
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
         * @return Response
         * @throws CaptchaNotFoundException
         * @throws DatabaseException
         * @throws InternalServerException
         */
        public function execute(Request $request): Response
        {
            $NetworkSession = new NetworkSession(SocialvoidRPC::$SocialvoidLib);

            $Response = Response::fromRequest($request);

            try
            {
                $Response->ResultData = $NetworkSession->getCaptcha()->createCaptcha($request->ClientIP, 120)->toArray();
            }
            catch(Exception $e)
            {
                // Allow standard errors
                if(Validate::isStandardError($e->getCode()))
                    throw $e;

                // If anything else, suppress the error.
                throw new InternalServerException('There was an unexpected error while trying to create a captcha instance', $e);
            }

            return $Response;
        }
    }
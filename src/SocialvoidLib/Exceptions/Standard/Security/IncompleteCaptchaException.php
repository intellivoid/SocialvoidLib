<?php

    namespace SocialvoidLib\Exceptions\Standard\Security;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class IncompleteCaptchaException extends Exception
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given captcha is incomplete", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::IncompleteCaptchaException, $previous);
        }
    }
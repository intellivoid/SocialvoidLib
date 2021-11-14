<?php

    namespace SocialvoidLib\Exceptions\Standard\Security;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class CaptchaExpiredException extends Exception
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The requested captcha has expired and cannot be used", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::CaptchaExpiredException, $previous);
        }
    }
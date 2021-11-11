<?php

    namespace SocialvoidLib\Exceptions\Standard\Security;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class CaptchaNotFoundException extends Exception
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The requested captcha was not found", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::CaptchaNotFoundException, $previous);
        }
    }
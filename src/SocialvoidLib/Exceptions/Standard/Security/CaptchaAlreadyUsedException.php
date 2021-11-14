<?php

    namespace SocialvoidLib\Exceptions\Standard\Security;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class CaptchaAlreadyUsedException extends Exception
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "Cannot use this captcha instance as it has already been used", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::CaptchaAlreadyUsedException, $previous);
        }
    }
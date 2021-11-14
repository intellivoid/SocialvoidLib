<?php

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class InvalidCaptchaIdException extends Exception
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given captcha ID is invalid", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidCaptchaIdException, $previous);
        }
    }
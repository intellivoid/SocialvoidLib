<?php

    namespace SocialvoidLib\Exceptions\Standard\Security;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class CaptchaAlreadyAnsweredException extends Exception
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "Cannot answer a captcha that has already been answered", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::CaptchaAlreadyAnsweredException, $previous);
        }
    }
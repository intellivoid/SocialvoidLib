<?php

    namespace SocialvoidLib\Exceptions\Standard\Security;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class CaptchaAnswerNotApplicableException extends Exception
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The captcha cannot be answered directly", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::CaptchaAnswerNotApplicableException, $previous);
        }
    }
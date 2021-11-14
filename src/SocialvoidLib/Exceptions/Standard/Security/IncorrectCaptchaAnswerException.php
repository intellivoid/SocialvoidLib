<?php

    namespace SocialvoidLib\Exceptions\Standard\Security;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class IncorrectCaptchaAnswerException extends Exception
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given answer to the captcha is incorrect", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::IncorrectCaptchaAnswerException, $previous);
            $this->message = $message;
        }
    }
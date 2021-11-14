<?php

    namespace SocialvoidLib\Exceptions\Standard\Security;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class CaptchaBlockedException extends Exception
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "This captcha cannot be used since the previous answer rendered this captcha blocked", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::CaptchaBlockedException, $previous);
        }
    }
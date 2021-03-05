<?php


    namespace SocialvoidLib\Exceptions\Standard\Authentication;


    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class IncorrectTwoFactorAuthenticationCodeException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class IncorrectTwoFactorAuthenticationCodeException extends \Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * IncorrectTwoFactorAuthenticationCodeException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::IncorrectTwoFactorAuthenticationCodeException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
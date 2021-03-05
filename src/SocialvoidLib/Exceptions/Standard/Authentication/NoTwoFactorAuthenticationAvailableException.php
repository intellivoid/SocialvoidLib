<?php


    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class NoTwoFactorAuthenticationAvailableException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class NoTwoFactorAuthenticationAvailableException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * NoTwoFactorAuthenticationAvailableException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::NoTwoFactorAuthenticationAvailableException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
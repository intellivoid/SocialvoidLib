<?php


    namespace SocialvoidLib\Exceptions\Standard\Authentication;


    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class NoPasswordAuthenticationAvailableException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class NoPasswordAuthenticationAvailableException extends Exception
    {
        /**
         * NoPasswordAuthenticationAvailableException constructor.
         *
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::NoPasswordAuthenticationAvailableException, $previous);
        }
    }
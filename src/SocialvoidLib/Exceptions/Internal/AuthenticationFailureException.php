<?php


    namespace SocialvoidLib\Exceptions\Internal;

    use Exception;
    use SocialvoidLib\Abstracts\InternalErrorCodes;
    use Throwable;

    /**
     * Class AuthenticationFailureException
     * @package SocialvoidLib\Exceptions\Internal
     */
    class AuthenticationFailureException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * AuthenticationFailureException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, InternalErrorCodes::AuthenticationFailureException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
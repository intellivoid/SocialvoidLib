<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class AuthenticationFailureException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
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
         * @noinspection PhpPureAttributeCanBeAddedInspection
         */
        public function __construct($message = "There was an unexpected error while trying to authenticate", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::AuthenticationFailureException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
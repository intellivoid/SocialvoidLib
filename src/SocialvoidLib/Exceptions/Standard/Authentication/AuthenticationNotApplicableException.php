<?php


    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class AuthenticationNotApplicableException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class AuthenticationNotApplicableException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * AuthenticationNotApplicableException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::AuthenticationNotApplicableException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
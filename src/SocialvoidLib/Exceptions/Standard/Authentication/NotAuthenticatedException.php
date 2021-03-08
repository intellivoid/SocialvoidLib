<?php


    namespace SocialvoidLib\Exceptions\Standard\Authentication;


    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class NotAuthenticatedException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class NotAuthenticatedException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * NotAuthenticatedException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::NotAuthenticatedException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
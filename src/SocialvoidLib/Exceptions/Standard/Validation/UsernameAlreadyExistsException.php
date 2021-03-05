<?php


    namespace SocialvoidLib\Exceptions\Standard\Validation;


    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class UsernameAlreadyExistsException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class UsernameAlreadyExistsException extends Exception
    {
        /**
         * @var string
         */
        private string $username;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * UsernameAlreadyExistsException constructor.
         * @param string $message
         * @param string $username
         * @param Throwable|null $previous
         */
        public function __construct($message = "", string $username="", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::UsernameAlreadyExistsException, $previous);
            $this->message = $message;
            $this->username = $username;
            $this->previous = $previous;
        }

        /**
         * @return string
         */
        public function getUsername(): string
        {
            return $this->username;
        }
    }
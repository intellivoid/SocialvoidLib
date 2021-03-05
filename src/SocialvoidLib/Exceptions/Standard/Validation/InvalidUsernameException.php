<?php


    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class InvalidUsernameException
     * @package SocialvoidLib\Exceptions\Standard
     */
    class InvalidUsernameException extends Exception
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
         * InvalidUsernameException constructor.
         * @param string $message
         * @param string $username
         * @param Throwable|null $previous
         */
        public function __construct($message = "", $username="", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidUsernameException, $previous);

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
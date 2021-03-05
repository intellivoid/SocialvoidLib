<?php


    namespace SocialvoidLib\Exceptions\Standard\Validation;


    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class InvalidPasswordException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidPasswordException extends Exception
    {
        /**
         * @var string
         */
        private string $password;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * InvalidPasswordException constructor.
         * @param string $message
         * @param string $password
         * @param Throwable|null $previous
         */
        public function __construct($message = "", $password="", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidPasswordException, $previous);
            $this->message = $message;
            $this->password = $password;
            $this->previous = $previous;
        }

        /**
         * @return string
         */
        public function getPassword(): string
        {
            return $this->password;
        }
    }
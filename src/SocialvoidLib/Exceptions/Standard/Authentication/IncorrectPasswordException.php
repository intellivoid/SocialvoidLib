<?php


    namespace SocialvoidLib\Exceptions\Standard\Authentication;


    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class IncorrectPasswordException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class IncorrectPasswordException extends \Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * IncorrectPasswordException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::IncorrectPasswordException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
<?php


    namespace SocialvoidLib\Exceptions\Standard\Validation;


    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class InvalidFirstNameException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidFirstNameException extends Exception
    {
        /**
         * @var string|null
         */
        private ?string $first_name;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * InvalidFirstNameException constructor.
         * @param string $message
         * @param string|null $first_name
         * @param Throwable|null $previous
         */
        public function __construct($message = "", string $first_name=null, Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidFirstNameException, $previous);
            $this->message = $message;
            $this->first_name = $first_name;
            $this->previous = $previous;
        }
    }
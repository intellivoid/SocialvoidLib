<?php

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class InvalidFileNameException extends \Exception
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given file name is invalid", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidFileNameException, $previous);
            $this->message = $message;
        }
    }
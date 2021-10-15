<?php

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class InvalidUrlValueException extends Exception
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given URL input is invalid", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidUrlValueException, $previous);
        }
    }
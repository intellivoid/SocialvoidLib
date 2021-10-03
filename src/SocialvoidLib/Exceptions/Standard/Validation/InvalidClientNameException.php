<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class InvalidClientNameException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidClientNameException extends Exception
    {

        /**
         * InvalidClientNameException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given client name is invalid", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidClientNameException, $previous);
            $this->message = $message;
        }
    }
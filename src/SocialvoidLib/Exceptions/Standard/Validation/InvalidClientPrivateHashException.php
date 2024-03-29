<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class InvalidClientPrivateHash
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidClientPrivateHashException extends Exception
    {

        /**
         * InvalidClientPrivateHash constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given Client Private hash is invalid", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidClientPrivateHashException, $previous);
            $this->message = $message;
        }
    }
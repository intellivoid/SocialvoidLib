<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class InvalidClientPublicHash
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidClientPublicHashException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * InvalidClientPublicHash constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given client public hash is invalid", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidClientPublicHashException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
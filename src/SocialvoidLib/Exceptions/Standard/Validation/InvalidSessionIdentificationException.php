<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class InvalidSessionIdentificationException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidSessionIdentificationException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * InvalidSessionIdentificationException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given session identification is invalid", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidSessionIdentificationException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
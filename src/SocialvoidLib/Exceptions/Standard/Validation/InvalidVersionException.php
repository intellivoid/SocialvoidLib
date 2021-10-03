<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class InvalidVersionException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidVersionException extends Exception
    {

        /**
         * InvalidVersionException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given version is invalid", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidVersionException, $previous);
            $this->message = $message;
        }
    }
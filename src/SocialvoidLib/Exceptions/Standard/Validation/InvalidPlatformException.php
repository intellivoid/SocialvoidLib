<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class InvalidPlatformException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidPlatformException extends Exception
    {

        /**
         * InvalidPlatformException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given platform is invalid", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidPlatformException, $previous);
            $this->message = $message;
        }
    }
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
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * InvalidClientPrivateHash constructor.
         * @param string $message
         * @param Throwable|null $previous
         * @noinspection PhpPureAttributeCanBeAddedInspection
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidClientPrivateHashException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
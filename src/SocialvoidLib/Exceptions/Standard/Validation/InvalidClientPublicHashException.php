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
         * @noinspection PhpPureAttributeCanBeAddedInspection
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidClientPublicHashException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
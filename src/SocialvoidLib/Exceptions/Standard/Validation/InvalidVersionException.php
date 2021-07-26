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
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * InvalidVersionException constructor.
         * @param string $message
         * @param Throwable|null $previous
         * @noinspection PhpPureAttributeCanBeAddedInspection
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidVersionException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
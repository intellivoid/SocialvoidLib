<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Definitions\ErrorDefinition;
    use Throwable;

    /**
     * Class InvalidClientPrivateHash
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidClientPrivateHashException extends Exception implements StandardErrorInterface
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

        /**
         * @inheritDoc
         */
        public static function getDefinition(): ErrorDefinition
        {
            return new ErrorDefinition(self::getName(), self::getDescription(), self::getErrorCode());
        }

        /**
         * @inheritDoc
         */
        public static function getName(): string
        {
            return 'InvalidClientPrivateHash';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The client\'s private hash is invalid and cannot be identified as a sha256';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::InvalidClientPrivateHashException;
        }
    }
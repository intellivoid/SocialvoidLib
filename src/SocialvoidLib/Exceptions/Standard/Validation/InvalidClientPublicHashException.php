<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Standard\ErrorDefinition;
    use Throwable;

    /**
     * Class InvalidClientPublicHash
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidClientPublicHashException extends Exception implements StandardErrorInterface
    {

        /**
         * InvalidClientPublicHash constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given client public hash is invalid", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidClientPublicHashException, $previous);
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
            return 'InvalidClientPublicHash';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The client\'s public hash is invalid and cannot be identified as a sha256';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::InvalidClientPublicHashException;
        }
    }
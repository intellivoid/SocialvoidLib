<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Definitions\ErrorDefinition;
    use Throwable;

    /**
     * Class InvalidVersionException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidVersionException extends Exception implements StandardErrorInterface
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
            return 'InvalidVersion';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The version is invalid or is too long, see the message for further details';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::InvalidVersionException;
        }
    }
<?php

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Standard\ErrorDefinition;
    use Throwable;

    class InvalidUrlValueException extends Exception implements StandardErrorInterface
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given URL input is invalid", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidUrlValueException, $previous);
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
            return 'InvalidUrlValue';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The given URL input is invalid';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::InvalidUrlValueException;
        }
    }
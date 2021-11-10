<?php

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Standard\ErrorDefinition;
    use Throwable;

    class InvalidGeoLocationException extends Exception implements StandardErrorInterface
    {
        public function __construct($message = "The given geo location input is invalid or too long", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidGeoLocationException, $previous);
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
            return 'InvalidGeoLocation';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The given geo location value is invalid or too long';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::InvalidGeoLocationException;
        }
    }
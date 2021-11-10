<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Standard\ErrorDefinition;
    use Throwable;

    /**
     * Class InvalidPlatformException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidPlatformException extends Exception implements StandardErrorInterface
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
            return 'InvalidPlatform';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The platform name contains invalid characters or is too long, see the message for further details';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::InvalidPlatformException;
        }
    }
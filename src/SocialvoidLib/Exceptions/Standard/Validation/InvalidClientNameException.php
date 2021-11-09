<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Definitions\ErrorDefinition;
    use Throwable;

    /**
     * Class InvalidClientNameException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidClientNameException extends Exception implements StandardErrorInterface
    {

        /**
         * InvalidClientNameException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given client name is invalid", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidClientNameException, $previous);
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
            return 'InvalidClientName';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The client name contains invalid characters or is too long, see the message for further details';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::InvalidClientNameException;
        }
    }
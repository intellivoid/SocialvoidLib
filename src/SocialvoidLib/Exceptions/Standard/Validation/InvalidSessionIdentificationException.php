<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Standard\ErrorDefinition;
    use Throwable;

    /**
     * Class InvalidSessionIdentificationException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidSessionIdentificationException extends Exception implements StandardErrorInterface
    {

        /**
         * InvalidSessionIdentificationException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given session identification is invalid", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidSessionIdentificationException, $previous);
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
            return 'InvalidSessionIdentification';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The session identification object is invalid, see the message for further details';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::InvalidSessionIdentificationException;
        }
    }
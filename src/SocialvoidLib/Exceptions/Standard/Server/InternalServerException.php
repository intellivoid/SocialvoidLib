<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Server;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Standard\ErrorDefinition;
    use Throwable;

    /**
     * Class InternalServerException
     * @package SocialvoidLib\Exceptions\Standard\Server
     */
    class InternalServerException extends Exception implements StandardErrorInterface
    {

        /**
         * InternalServerException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "There was an unexpected error while trying to handle your request", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InternalServerError, $previous);
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
            return 'InternalServerError';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'Raised when there was an unexpected error while trying to process your request.';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::InternalServerError;
        }
    }
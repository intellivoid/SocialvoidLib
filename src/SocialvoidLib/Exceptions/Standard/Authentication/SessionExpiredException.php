<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Definitions\ErrorDefinition;
    use Throwable;

    /**
     * Class SessionExpiredException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class SessionExpiredException extends Exception implements StandardErrorInterface
    {

        /**
         * SessionExpiredException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The session has expired", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::SessionExpiredException, $previous);
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
            return 'SessionExpired';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'Raised when trying to use a session that has expired';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::SessionExpiredException;
        }
    }
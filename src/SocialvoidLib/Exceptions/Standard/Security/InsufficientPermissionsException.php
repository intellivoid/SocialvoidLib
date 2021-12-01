<?php

    namespace SocialvoidLib\Exceptions\Standard\Security;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Standard\ErrorDefinition;
    use Throwable;

    class InsufficientPermissionsException extends Exception implements StandardErrorInterface
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "Your session has insufficient permissions to execute this method", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InsufficientPermissionsException, $previous);
        }

        /**
         * @inheritDoc
         */
        public static function getName(): string
        {
            return 'InsufficientPermissions';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'Raises when executing a method when your current session has insufficient permissions to execute it';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::InsufficientPermissionsException;
        }

        /**
         * @inheritDoc
         */
        public static function getDefinition(): ErrorDefinition
        {
            return new ErrorDefinition(self::getName(), self::getDescription(), self::getErrorCode());
        }
    }
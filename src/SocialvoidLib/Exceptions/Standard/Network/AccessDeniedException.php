<?php

    namespace SocialvoidLib\Exceptions\Standard\Network;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Definitions\ErrorDefinition;
    use Throwable;

    class AccessDeniedException extends Exception implements StandardErrorInterface
    {

        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "Insufficient permissions to access this resource", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::AccessDeniedException, $previous);
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
            return 'AccessDenied';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The authenticated peer does not have sufficient permissions to access the requested resource or to invoke a restricted method';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::AccessDeniedException;
        }
    }
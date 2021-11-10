<?php

    namespace SocialvoidLib\Exceptions\Standard\Network;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Standard\ErrorDefinition;
    use Throwable;

    class SelfInteractionNotPermittedException extends Exception implements StandardErrorInterface
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = 'Self interaction is not permitted with this method', Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::SelfInteractionNotPermittedException, $previous);
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
            return 'SelfInteractionNotPermitted';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'Raised when attempting to invoke a method or change that involves a peer that you are authenticated as such as following a peer that you are authenticated as';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::SelfInteractionNotPermittedException;
        }
    }
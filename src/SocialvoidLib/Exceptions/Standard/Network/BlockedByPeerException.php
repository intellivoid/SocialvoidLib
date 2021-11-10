<?php

    namespace SocialvoidLib\Exceptions\Standard\Network;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Standard\ErrorDefinition;
    use Throwable;

    class BlockedByPeerException extends Exception implements StandardErrorInterface
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The requested peer has blocked you", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::BlockedByPeerException, $previous);
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
            return 'BlockedByPeer';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'Raised when attempting to interact with a peer that blocked you';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::BlockedByPeerException;
        }
    }
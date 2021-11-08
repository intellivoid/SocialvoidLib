<?php

    namespace SocialvoidLib\Exceptions\Standard\Network;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Definitions\ErrorDefinition;
    use Throwable;

    class BlockedPeerException extends Exception implements StandardErrorInterface
    {
        public function __construct($message = "You cannot interact with a peer that you blocked", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::BlockedPeerException, $previous);
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
            return 'BlockedPeer';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'Raised when attempting to interact with a peer that you blocked';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::BlockedPeerException;
        }
    }
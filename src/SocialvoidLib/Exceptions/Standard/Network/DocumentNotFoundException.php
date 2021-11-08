<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Network;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Definitions\ErrorDefinition;
    use Throwable;

    class DocumentNotFoundException extends Exception implements StandardErrorInterface
    {

        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The requested document was not found on the server", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::DocumentNotFoundException, $previous);
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
            return 'DocumentNotFound';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The requested Document ID was not found on the server';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::DocumentNotFoundException;
        }
    }
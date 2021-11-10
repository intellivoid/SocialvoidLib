<?php

    namespace SocialvoidLib\Exceptions\Standard\Server;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Standard\ErrorDefinition;
    use Throwable;

    class DocumentUploadException extends Exception implements StandardErrorInterface
    {

        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "There was an error while trying to process the file upload", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::DocumentUploadException, $previous);
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
            return 'DocumentUpload';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'Raised when there was an error while trying to process the document upload';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::DocumentUploadException;
        }
    }
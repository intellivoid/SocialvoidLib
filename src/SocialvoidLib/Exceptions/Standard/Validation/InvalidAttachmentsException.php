<?php

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class InvalidAttachmentsException extends \Exception
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given attachments are invalid", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidAttachmentsException, $previous);
            $this->message = $message;
        }
    }
<?php

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class TooManyAttachmentsException extends Exception
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "There are too many attachments", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::TooManyAttachmentsException, $previous);
            $this->message = $message;
        }
    }
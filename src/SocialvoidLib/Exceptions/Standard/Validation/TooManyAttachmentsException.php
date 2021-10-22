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
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::TooManyAttachmentsException, $previous);
            $this->message = $message;
        }
    }
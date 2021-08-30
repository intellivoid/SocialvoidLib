<?php

    namespace SocialvoidLib\Exceptions\Standard\Server;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class DocumentUploadException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "There was an error while trying to process the file upload", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::DocumentUploadException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
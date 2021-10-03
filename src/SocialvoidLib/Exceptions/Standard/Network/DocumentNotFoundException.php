<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Network;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class DocumentNotFoundException extends Exception
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
    }
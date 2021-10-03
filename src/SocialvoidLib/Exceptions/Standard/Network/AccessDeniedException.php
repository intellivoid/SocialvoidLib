<?php

    namespace SocialvoidLib\Exceptions\Standard\Network;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class AccessDeniedException extends Exception
    {

        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "Insufficient permissions to access this resource", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::AccessDeniedException, $previous);
            $this->message = $message;
        }
    }
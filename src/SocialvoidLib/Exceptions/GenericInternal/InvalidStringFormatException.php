<?php

    namespace SocialvoidLib\Exceptions\GenericInternal;

    use Exception;
    use Throwable;

    class InvalidStringFormatException extends Exception
    {

        /**
         * @param string $message
         * @param int $code
         * @param Throwable|null $previous
         */
        public function __construct($message = "", $code = 0, Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
            $this->message = $message;
            $this->code = $code;
        }
    }
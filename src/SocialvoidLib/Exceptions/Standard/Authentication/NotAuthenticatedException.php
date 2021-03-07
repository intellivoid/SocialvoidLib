<?php


    namespace SocialvoidLib\Exceptions\Standard\Authentication;


    use Exception;
    use Throwable;

    class NotAuthenticatedException extends Exception
    {
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }
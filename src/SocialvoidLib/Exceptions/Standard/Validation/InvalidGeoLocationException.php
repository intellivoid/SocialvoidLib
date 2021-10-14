<?php

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class InvalidGeoLocationException extends Exception
    {
        public function __construct($message = "The given geo location input is invalid or too long", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidGeoLocationException, $previous);
        }
    }
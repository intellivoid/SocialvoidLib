<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class SessionExpiredException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class SessionExpiredException extends Exception
    {

        /**
         * SessionExpiredException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The session has expired", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::SessionExpiredException, $previous);
            $this->message = $message;
        }
    }
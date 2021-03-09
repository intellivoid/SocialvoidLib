<?php


    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class SessionNotFoundException
     * @package SocialvoidLib\Exceptions\Standard\Network
     */
    class SessionNotFoundException extends Exception
    {
        /**
         * SessionNotFoundException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::SessionNotFoundException, $previous);
        }
    }
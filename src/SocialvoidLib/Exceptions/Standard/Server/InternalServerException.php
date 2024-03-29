<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Server;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class InternalServerException
     * @package SocialvoidLib\Exceptions\Standard\Server
     */
    class InternalServerException extends Exception
    {

        /**
         * InternalServerException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "There was an unexpected error while trying to handle your request", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InternalServerError, $previous);
            $this->message = $message;
        }
    }
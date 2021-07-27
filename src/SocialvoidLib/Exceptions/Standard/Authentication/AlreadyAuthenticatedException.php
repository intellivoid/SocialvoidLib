<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class AlreadyAuthenticatedException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class AlreadyAuthenticatedException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * AlreadyAuthenticatedException constructor.
         * @param string $message
         * @param Throwable|null $previous
         * @noinspection PhpPureAttributeCanBeAddedInspection
         */
        public function __construct($message = "The session is already authenticated to the network", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::AlreadyAuthenticatedException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
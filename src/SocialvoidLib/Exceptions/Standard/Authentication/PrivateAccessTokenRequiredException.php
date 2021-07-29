<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class PrivateAccessTokenRequiredException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class PrivateAccessTokenRequiredException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * PrivateAccessTokenRequiredException constructor.
         * @param string $message
         * @param Throwable|null $previous
         * @noinspection PhpPureAttributeCanBeAddedInspection
         */
        public function __construct($message = "This entity can only be authenticated using a private access token", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::PrivateAccessTokenRequiredException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
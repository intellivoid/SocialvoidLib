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
         * @param int $code
         * @param Throwable|null $previous
         * @noinspection PhpPureAttributeCanBeAddedInspection
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::PrivateAccessTokenRequiredException, $previous);
            $this->message = $message;
            $this->code = $code;
            $this->previous = $previous;
        }
    }
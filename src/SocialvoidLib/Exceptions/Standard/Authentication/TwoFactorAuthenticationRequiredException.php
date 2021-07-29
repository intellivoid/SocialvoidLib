<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class TwoFactorAuthenticationRequiredException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class TwoFactorAuthenticationRequiredException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * TwoFactorAuthenticationRequiredException constructor.
         * @param string $message
         * @param Throwable|null $previous
         * @noinspection PhpPureAttributeCanBeAddedInspection
         */
        public function __construct($message = "Two Factor Authentication is required to authenticate", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::TwoFactorAuthenticationRequiredException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
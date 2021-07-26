<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Internal;

    use Exception;
    use SocialvoidLib\Abstracts\InternalErrorCodes;
    use Throwable;

    /**
     * Class TwoFactorAuthenticationRequiredException
     * @package SocialvoidLib\Exceptions\Internal
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
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, InternalErrorCodes::TwoFactorAuthenticationRequiredException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
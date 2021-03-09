<?php


    namespace SocialvoidLib\Exceptions\Internal;


    use Exception;
    use SocialvoidLib\Abstracts\InternalErrorCodes;
    use SocialvoidLib\Objects\User\UserAuthenticationProperties;
    use Throwable;

    /**
     * Class NoTimeBasedSignatureAvailableException
     * @package SocialvoidLib\Exceptions\Internal
     */
    class NoTimeBasedSignatureAvailableException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;
        /**
         * @var UserAuthenticationProperties|null
         */
        private ?UserAuthenticationProperties $userAuthenticationProperties;

        /**
         * NoTimeBasedSignatureAvailableException constructor.
         * @param string $message
         * @param UserAuthenticationProperties|null $userAuthenticationProperties
         * @param Throwable|null $previous
         */
        public function __construct($message = "", UserAuthenticationProperties $userAuthenticationProperties=null, Throwable $previous = null)
        {
            parent::__construct($message, InternalErrorCodes::NoTimeBasedSignatureAvailableException, $previous);
            $this->message = $message;
            $this->previous = $previous;
            $this->userAuthenticationProperties = $userAuthenticationProperties;
        }

        /**
         * @return UserAuthenticationProperties|null
         */
        public function getUserAuthenticationProperties(): ?UserAuthenticationProperties
        {
            return $this->userAuthenticationProperties;
        }
    }
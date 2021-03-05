<?php


    namespace SocialvoidLib\Exceptions\Internal;


    use Exception;
    use SocialvoidLib\Abstracts\InternalErrorCodes;
    use SocialvoidLib\Objects\User\UserAuthenticationProperties;
    use Throwable;

    /**
     * Class NoRecoveryCodesAvailableException
     * @package SocialvoidLib\Exceptions\Internal
     */
    class NoRecoveryCodesAvailableException extends Exception
    {
        /**
         * @var UserAuthenticationProperties|null
         */
        private ?UserAuthenticationProperties $userAuthenticationProperties;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * NoRecoveryCodesAvailableException constructor.
         * @param string $message
         * @param UserAuthenticationProperties|null $userAuthenticationProperties
         * @param Throwable|null $previous
         */
        public function __construct(string $message = "", UserAuthenticationProperties $userAuthenticationProperties=null, Throwable $previous = null)
        {
            parent::__construct($message, InternalErrorCodes::NoRecoveryCodesAvailableException, $previous);
            $this->message = $message;
            $this->userAuthenticationProperties = $userAuthenticationProperties;
            $this->previous = $previous;
        }

        /**
         * @return UserAuthenticationProperties|null
         */
        public function getUserAuthenticationProperties(): ?UserAuthenticationProperties
        {
            return $this->userAuthenticationProperties;
        }
    }
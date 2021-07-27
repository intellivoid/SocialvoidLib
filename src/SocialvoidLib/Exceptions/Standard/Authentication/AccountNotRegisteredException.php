<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class AccountNotRegisteredException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class AccountNotRegisteredException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * AccountNotRegisteredException constructor.
         * @param string $message
         * @param Throwable|null $previous
         * @noinspection PhpPureAttributeCanBeAddedInspection
         */
        public function __construct($message = "The account is not registered in the network", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::AccountNotRegisteredException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Definitions\ErrorDefinition;
    use Throwable;

    /**
     * Class AuthenticationFailureException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class AuthenticationFailureException extends Exception implements StandardErrorInterface
    {

        /**
         * AuthenticationFailureException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "There was an unexpected error while trying to authenticate", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::AuthenticationFailureException, $previous);
            $this->message = $message;
        }

        /**
         * @inheritDoc
         */
        public static function getDefinition(): ErrorDefinition
        {
            return new ErrorDefinition(self::getName(), self::getDescription(), self::getErrorCode());
        }

        /**
         * @inheritDoc
         */
        public static function getName(): string
        {
            return 'AuthenticationFailure';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The authentication process failed for some unexpected reason, see the message for further details';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::AuthenticationFailureException;
        }
    }
<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Standard\ErrorDefinition;
    use Throwable;

    /**
     * Class AlreadyAuthenticatedException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class AlreadyAuthenticatedException extends Exception implements StandardErrorInterface
    {

        /**
         * AlreadyAuthenticatedException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The session is already authenticated to the network", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::AlreadyAuthenticatedException, $previous);
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
            return 'AlreadyAuthenticated';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The client is attempting to authenticate when already authenticated';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::AlreadyAuthenticatedException;
        }
    }
<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Standard\ErrorDefinition;
    use Throwable;

    /**
     * Class PrivateAccessTokenRequiredException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class PrivateAccessTokenRequiredException extends Exception implements StandardErrorInterface
    {

        /**
         * PrivateAccessTokenRequiredException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "This entity can only be authenticated using a private access token", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::PrivateAccessTokenRequiredException, $previous);
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
            return 'PrivateAccessTokenRequired';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'Raised when the user/entity uses a Private Access Token to authenticate and the client attempted to authenticate in another way';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::PrivateAccessTokenRequiredException;
        }
    }
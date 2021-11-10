<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Standard\ErrorDefinition;
    use Throwable;

    /**
     * Class TwoFactorAuthenticationRequiredException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class TwoFactorAuthenticationRequiredException extends Exception implements StandardErrorInterface
    {

        /**
         * TwoFactorAuthenticationRequiredException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "Two Factor Authentication is required to authenticate", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::TwoFactorAuthenticationRequiredException, $previous);
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
            return 'TwoFactorAuthenticationRequired';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'Two-Factor Authentication is required, the client must repeat the same request but provide a Two-Factor authentication code as well';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::TwoFactorAuthenticationRequiredException;
        }
    }
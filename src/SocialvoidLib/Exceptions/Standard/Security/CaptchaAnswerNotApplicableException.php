<?php

    namespace SocialvoidLib\Exceptions\Standard\Security;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Standard\ErrorDefinition;
    use Throwable;

    class CaptchaAnswerNotApplicableException extends Exception implements StandardErrorInterface
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The captcha cannot be answered directly", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::CaptchaAnswerNotApplicableException, $previous);
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
            return 'CaptchaAnswerNotApplicable';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'Raised when the captcha does not accept answers from the client';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::CaptchaAnswerNotApplicableException;
        }
    }
<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Definitions\ErrorDefinition;
    use Throwable;

    /**
     * Class BadSessionChallengeAnswerException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class BadSessionChallengeAnswerException extends Exception implements StandardErrorInterface
    {

        /**
         * BadSessionChallengeAnswerException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The session challenge answer is incorrect", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::BadSessionChallengeAnswerException, $previous);
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
            return 'BadSessionChallengeAnswer';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The given session challenge answer is incorrect or out of sync';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::BadSessionChallengeAnswerException;
        }
    }
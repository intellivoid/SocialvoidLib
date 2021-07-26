<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class BadSessionChallengeAnswerException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class BadSessionChallengeAnswerException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * BadSessionChallengeAnswerException constructor.
         * @param string $message
         * @param Throwable|null $previous
         * @noinspection PhpPureAttributeCanBeAddedInspection
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::BadSessionChallengeAnswerException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
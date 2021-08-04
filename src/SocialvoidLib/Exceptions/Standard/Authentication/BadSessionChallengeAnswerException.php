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
         */
        public function __construct($message = "The session challenge answer is incorrect", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::BadSessionChallengeAnswerException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
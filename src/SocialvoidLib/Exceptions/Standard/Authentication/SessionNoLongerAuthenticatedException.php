<?php

    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Objects\ActiveSession;
    use Throwable;

    /**
     * Class SessionNoLongerAuthenticatedException
     * @package SocialvoidLib\Exceptions\Standard\Network
     */
    class SessionNoLongerAuthenticatedException extends Exception
    {
        /**
         * @var ActiveSession|null
         */
        private ?ActiveSession $activeSession;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * SessionNoLongerAuthenticatedException constructor.
         * @param string $message
         * @param ActiveSession|null $activeSession
         * @param Throwable|null $previous
         */
        public function __construct($message = "", ActiveSession $activeSession=null, Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::SessionNoLongerAuthenticatedException, $previous);
            $this->message = $message;
            $this->activeSession = $activeSession;
            $this->previous = $previous;
        }

        /**
         * @return ActiveSession|null
         */
        public function getActiveSession(): ?ActiveSession
        {
            return $this->activeSession;
        }
    }
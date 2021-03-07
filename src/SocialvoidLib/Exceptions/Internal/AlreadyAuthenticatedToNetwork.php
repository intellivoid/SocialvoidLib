<?php

    namespace SocialvoidLib\Exceptions\Internal;


    use Exception;
    use SocialvoidLib\Abstracts\InternalErrorCodes;
    use SocialvoidLib\NetworkSession;
    use Throwable;

    /**
     * Class AlreadyAuthenticatedToNetwork
     * @package SocialvoidLib\Exceptions\Internal
     */
    class AlreadyAuthenticatedToNetwork extends Exception
    {
        /**
         * @var NetworkSession|null
         */
        private ?NetworkSession $network;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * AlreadyAuthenticatedToNetwork constructor.
         * @param string $message
         * @param NetworkSession|null $network
         * @param Throwable|null $previous
         */
        public function __construct($message = "", NetworkSession $network=null, Throwable $previous = null)
        {
            parent::__construct($message, InternalErrorCodes::AlreadyAuthenticatedToNetwork, $previous);
            $this->message = $message;
            $this->network = $network;
            $this->previous = $previous;
        }

        /**
         * @return NetworkSession|null
         */
        public function getNetwork(): ?NetworkSession
        {
            return $this->network;
        }
    }
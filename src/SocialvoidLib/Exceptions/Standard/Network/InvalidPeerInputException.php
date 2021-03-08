<?php


    namespace SocialvoidLib\Exceptions\Standard\Network;


    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class InvalidPeerInputException
     * @package SocialvoidLib\Exceptions\Standard\Network
     */
    class InvalidPeerInputException extends Exception
    {
        /**
         * @var mixed
         */
        private $peer;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * InvalidPeerInputException constructor.
         * @param string $message
         * @param null $peer
         */
        public function __construct($message = "", $peer=null, Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidPeerInputException, $previous);
            $this->message = $message;
            $this->peer = $peer;
            $this->previous = $previous;
        }

        /**
         * @return mixed
         */
        public function getPeer()
        {
            return $this->peer;
        }
    }
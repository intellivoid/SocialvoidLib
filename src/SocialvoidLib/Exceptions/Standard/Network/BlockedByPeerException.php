<?php

    namespace SocialvoidLib\Exceptions\Standard\Network;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class BlockedByPeerException extends Exception
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The requested peer has blocked you", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::BlockedByPeerException, $previous);
        }
    }
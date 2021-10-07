<?php

    namespace SocialvoidLib\Exceptions\Standard\Network;

    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class BlockedPeerException extends \Exception
    {
        public function __construct($message = "You cannot interact with a peer that you blocked", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::BlockedPeerException, $previous);
        }
    }
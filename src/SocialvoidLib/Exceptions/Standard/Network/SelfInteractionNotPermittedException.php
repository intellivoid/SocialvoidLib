<?php

    namespace SocialvoidLib\Exceptions\Standard\Network;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    class SelfInteractionNotPermittedException extends Exception
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = 'Self interaction is not permitted with this method', Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::SelfInteractionNotPermittedException, $previous);
            $this->message = $message;
        }
    }
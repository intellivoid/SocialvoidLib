<?php


    namespace SocialvoidLib\Exceptions\Standard\Network;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class PostNotFoundException
     * @package SocialvoidLib\Exceptions\Standard\Network
     */
    class PostNotFoundException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * PostNotFoundException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::PostNotFoundException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
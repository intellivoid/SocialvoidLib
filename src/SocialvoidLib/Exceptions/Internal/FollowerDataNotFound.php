<?php


    namespace SocialvoidLib\Exceptions\Internal;


    use Exception;
    use SocialvoidLib\Abstracts\InternalErrorCodes;
    use Throwable;

    /**
     * Class FollowerDataNotFound
     * @package SocialvoidLib\Exceptions\Internal
     */
    class FollowerDataNotFound extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * FollowerDataNotFound constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, InternalErrorCodes::FollowerDataNotFound, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
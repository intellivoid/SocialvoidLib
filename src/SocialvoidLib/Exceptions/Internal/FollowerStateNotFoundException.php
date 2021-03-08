<?php


    namespace SocialvoidLib\Exceptions\Internal;


    use SocialvoidLib\Abstracts\InternalErrorCodes;
    use Throwable;

    /**
     * Class FollowerStateNotFoundException
     * @package SocialvoidLib\Exceptions\Internal
     */
    class FollowerStateNotFoundException extends \Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * FollowerStateNotFoundException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, InternalErrorCodes::FollowerStateNotFoundException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
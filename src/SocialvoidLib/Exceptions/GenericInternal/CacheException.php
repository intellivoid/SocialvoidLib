<?php


    namespace SocialvoidLib\Exceptions\GenericInternal;


    use Exception;
    use Throwable;

    /**
     * Class CacheException
     * @package SocialvoidLib\Exceptions\GenericInternal
     */
    class CacheException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * CacheException constructor.
         * @param string $message
         * @param int $code
         * @param Throwable|null $previous
         */
        public function __construct($message = "", $code = 0, Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
            $this->message = $message;
            $this->code = $code;
            $this->previous = $previous;
        }
    }
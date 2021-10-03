<?php


    namespace SocialvoidLib\Exceptions\GenericInternal;

    use Exception;
    use Throwable;

    /**
     * Class CacheMissedException
     * @package SocialvoidLib\Exceptions\GenericInternal
     */
    class CacheMissedException extends Exception
    {

        /**
         * CacheMissedException constructor.
         * @param string $message
         * @param int $code
         * @param Throwable|null $previous
         */
        public function __construct($message = "", $code = 0, Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
            $this->message = $message;
            $this->code = $code;
        }
    }
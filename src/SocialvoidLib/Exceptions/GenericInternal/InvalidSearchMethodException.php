<?php

    namespace SocialvoidLib\Exceptions\GenericInternal;


    use Exception;
    use Throwable;

    /**
     * Class InvalidSearchMethodException
     * @package SocialvoidLib\Exceptions\GenericInternal
     */
    class InvalidSearchMethodException extends Exception
    {
        /**
         * @var string
         */
        private string $search_method;

        /**
         * @var string
         */
        private string $value;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * InvalidSearchMethodException constructor.
         * @param string $message
         * @param string $search_method
         * @param string $value
         * @param Throwable|null $previous
         */
        public function __construct($message = "", string $search_method="", string $value="", Throwable $previous = null)
        {
            parent::__construct($message, 0, $previous);
            $this->message = $message;
            $this->search_method = $search_method;
            $this->value = $value;
            $this->previous = $previous;
        }

        /**
         * @return string
         */
        public function getSearchMethod(): string
        {
            return $this->search_method;
        }

        /**
         * @return string
         */
        public function getValue(): string
        {
            return $this->value;
        }
    }
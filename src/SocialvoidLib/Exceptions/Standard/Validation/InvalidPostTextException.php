<?php


    namespace SocialvoidLib\Exceptions\Standard\Validation;


    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class InvalidPostTextException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidPostTextException extends \Exception
    {
        /**
         * @var string|null
         */
        private ?string $text;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * InvalidPostTextException constructor.
         * @param string $message
         * @param string|null $text
         * @param Throwable|null $previous
         */
        public function __construct($message = "", string $text=null, Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidPostTextException, $previous);
            $this->message = $message;
            $this->text = $text;
            $this->previous = $previous;
        }
    }
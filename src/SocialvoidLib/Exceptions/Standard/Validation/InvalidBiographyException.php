<?php


    namespace SocialvoidLib\Exceptions\Standard\Validation;


    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class InvalidBiographyException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidBiographyException extends Exception
    {
        /**
         * @var string|null
         */
        private ?string $biography;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * InvalidBiographyException constructor.
         * @param string $message
         * @param string|null $biography
         * @param Throwable|null $previous
         */
        public function __construct($message = "", string $biography=null, Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidBiographyException, $previous);
            $this->message = $message;
            $this->biography = $biography;
            $this->previous = $previous;
        }
    }
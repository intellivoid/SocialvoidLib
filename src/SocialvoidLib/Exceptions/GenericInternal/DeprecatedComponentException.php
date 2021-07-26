<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\GenericInternal;

    use Exception;
    use Throwable;

    /**
     * Class DeprecatedComponentException
     * @package SocialvoidLib\Exceptions\GenericInternal
     */
    class DeprecatedComponentException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * DeprecatedComponentException constructor.
         * @param string $message
         * @param int $code
         * @param Throwable|null $previous
         * @noinspection PhpPureAttributeCanBeAddedInspection
         */
        public function __construct($message = "", $code = 0, Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
            $this->message = $message;
            $this->code = $code;
            $this->previous = $previous;
        }
    }
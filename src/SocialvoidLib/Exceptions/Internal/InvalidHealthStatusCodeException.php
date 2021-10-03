<?php

    /** @noinspection PhpRedundantDocCommentInspection */
    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace SocialvoidLib\Exceptions\Internal;

    use Exception;
    use SocialvoidLib\Abstracts\InternalErrorCodes;
    use Throwable;

    class InvalidHealthStatusCodeException extends Exception
    {

        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, InternalErrorCodes::InvalidHealthStatusCodeException, $previous);
            $this->message = $message;
        }
    }
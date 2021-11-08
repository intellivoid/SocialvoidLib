<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Exceptions\Standard\Network;


    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Definitions\ErrorDefinition;
    use Throwable;

    /**
     * Class FileUploadException
     * @package SocialvoidLib\Exceptions\Standard\Network
     */
    class FileUploadException extends Exception implements StandardErrorInterface
    {

        /**
         * FileUploadException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "There was an unexpected issue while trying to handle your file upload", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::FileUploadErrorException, $previous);
            $this->message = $message;
        }

        /**
         * @inheritDoc
         */
        public static function getDefinition(): ErrorDefinition
        {
            return new ErrorDefinition(self::getName(), self::getDescription(), self::getErrorCode());
        }

        /**
         * @inheritDoc
         */
        public static function getName(): string
        {
            return 'FileUploadError';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'Raised when there was an error while trying to upload one or more files to the network';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::FileUploadErrorException;
        }
    }
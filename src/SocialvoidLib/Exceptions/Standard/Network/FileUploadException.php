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


    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class FileUploadException
     * @package SocialvoidLib\Exceptions\Standard\Network
     */
    class FileUploadException extends \Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * FileUploadException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "There was an unexpected issue while trying to handle your file upload", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::FileUploadErrorException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
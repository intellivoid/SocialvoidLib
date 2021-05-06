<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Exceptions\GenericInternal;


    use Throwable;

    /**
     * Class FileNotFoundException
     * @package SocialvoidLib\Exceptions\GenericInternal
     */
    class FileNotFoundException extends \Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * @var string|null
         */
        private ?string $filename;

        /**
         * FileNotFoundException constructor.
         * @param string $message
         * @param string|null $filename
         * @param int $code
         * @param Throwable|null $previous
         */
        public function __construct($message = "", ?string $filename = null, $code = 0, Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
            $this->message = $message;
            $this->code = $code;
            $this->previous = $previous;
            $this->filename = $filename;
        }

        /**
         * @return string|null
         */
        public function getFilename(): ?string
        {
            return $this->filename;
        }
    }
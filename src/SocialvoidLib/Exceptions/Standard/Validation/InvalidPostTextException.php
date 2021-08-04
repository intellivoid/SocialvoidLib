<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

namespace SocialvoidLib\Exceptions\Standard\Validation;


    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class InvalidPostTextException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidPostTextException extends Exception
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
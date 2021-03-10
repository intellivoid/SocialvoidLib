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
     * Class InvalidLastNameException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidLastNameException extends Exception
    {
        /**
         * @var string|null
         */
        private ?string $last_name;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * InvalidLastNameException constructor.
         * @param string $message
         * @param string|null $last_name
         * @param Throwable|null $previous
         */
        public function __construct($message = "", string $last_name=null, Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidLastNameException, $previous);
            $this->message = $message;
            $this->last_name = $last_name;
            $this->previous = $previous;
        }

        /**
         * @return string|null
         */
        public function getLastName(): ?string
        {
            return $this->last_name;
        }
    }
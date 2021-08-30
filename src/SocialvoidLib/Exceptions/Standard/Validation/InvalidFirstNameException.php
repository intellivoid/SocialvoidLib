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
     * Class InvalidFirstNameException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidFirstNameException extends Exception
    {
        /**
         * @var string|null
         */
        private ?string $first_name;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * InvalidFirstNameException constructor.
         * @param string $message
         * @param string|null $first_name
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given first name is invalid", string $first_name=null, Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidFirstNameException, $previous);
            $this->message = $message;
            $this->first_name = $first_name;
            $this->previous = $previous;
        }
    }
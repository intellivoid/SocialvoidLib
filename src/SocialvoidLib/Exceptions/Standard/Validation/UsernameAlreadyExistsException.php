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
     * Class UsernameAlreadyExistsException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class UsernameAlreadyExistsException extends Exception
    {
        /**
         * @var string
         */
        private string $username;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * UsernameAlreadyExistsException constructor.
         * @param string $message
         * @param string $username
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given username is already used on the network", string $username="", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::UsernameAlreadyExistsException, $previous);
            $this->message = $message;
            $this->username = $username;
            $this->previous = $previous;
        }

        /**
         * @return string
         */
        public function getUsername(): string
        {
            return $this->username;
        }
    }
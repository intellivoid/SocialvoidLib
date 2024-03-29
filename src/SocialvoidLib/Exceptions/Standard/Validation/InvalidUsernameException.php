<?php

/** @noinspection PhpPropertyOnlyWrittenInspection */

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
     * Class InvalidUsernameException
     * @package SocialvoidLib\Exceptions\Standard
     */
    class InvalidUsernameException extends Exception
    {
        /**
         * @var string|null|mixed
         */
        private $username;


        /**
         * InvalidUsernameException constructor.
         * @param string $message
         * @param string $username
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given username is invalid", $username="", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidUsernameException, $previous);

            $this->message = $message;
            $this->username = $username;
        }

        /**
         * @return string
         */
        public function getUsername(): string
        {
            return $this->username;
        }
    }
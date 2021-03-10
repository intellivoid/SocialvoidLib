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
     * Class InvalidPeerInputException
     * @package SocialvoidLib\Exceptions\Standard\Network
     */
    class InvalidPeerInputException extends Exception
    {
        /**
         * @var mixed
         */
        private $peer;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * InvalidPeerInputException constructor.
         * @param string $message
         * @param null $peer
         * @param Throwable|null $previous
         */
        public function __construct($message = "", $peer=null, Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidPeerInputException, $previous);
            $this->message = $message;
            $this->peer = $peer;
            $this->previous = $previous;
        }

        /**
         * @return mixed
         */
        public function getPeer()
        {
            return $this->peer;
        }
    }
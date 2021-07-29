<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

namespace SocialvoidLib\Exceptions\Standard\Authentication;


    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class IncorrectTwoFactorAuthenticationCodeException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class IncorrectTwoFactorAuthenticationCodeException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * IncorrectTwoFactorAuthenticationCodeException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given Two Factor Authentication code is incorrect", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::IncorrectTwoFactorAuthenticationCodeException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
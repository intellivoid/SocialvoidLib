<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

namespace SocialvoidLib\Exceptions\Internal;


    use Exception;
    use SocialvoidLib\Abstracts\InternalErrorCodes;
    use SocialvoidLib\Objects\User\UserAuthenticationProperties;
    use Throwable;

    /**
     * Class TimeBasedPrivateSignatureAlreadyExistsException
     * @package SocialvoidLib\Exceptions\Internal
     */
    class TimeBasedPrivateSignatureAlreadyExistsException extends Exception
    {
        /**
         * @var UserAuthenticationProperties|null
         */
        private ?UserAuthenticationProperties $userAuthenticationProperties;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * TimeBasedPrivateSignatureAlreadyExistsException constructor.
         * @param string $message
         * @param UserAuthenticationProperties|null $userAuthenticationProperties
         * @param Throwable|null $previous
         */
        public function __construct($message = "", UserAuthenticationProperties $userAuthenticationProperties=null, Throwable $previous = null)
        {
            parent::__construct($message, InternalErrorCodes::TimeBasedPrivateSignatureAlreadyExistsException, $previous);
            $this->message = $message;
            $this->userAuthenticationProperties = $userAuthenticationProperties;
            $this->previous = $previous;
        }
    }
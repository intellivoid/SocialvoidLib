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
     * Class NotAuthenticatedException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class NotAuthenticatedException extends Exception
    {

        /**
         * NotAuthenticatedException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "You must be authenticated to perform this action", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::NotAuthenticatedException, $previous);
            $this->message = $message;
        }
    }
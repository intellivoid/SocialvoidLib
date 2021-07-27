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
     * Class SessionNotFoundException
     * @package SocialvoidLib\Exceptions\Standard\Network
     */
    class SessionNotFoundException extends Exception
    {
        /**
         * SessionNotFoundException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The requested session was not found in the network", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::SessionNotFoundException, $previous);
        }
    }
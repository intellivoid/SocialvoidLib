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
    use SocialvoidLib\Objects\ActiveSession;
    use Throwable;

    /**
     * Class SessionNoLongerAuthenticatedException
     * @package SocialvoidLib\Exceptions\Standard\Network
     */
    class SessionNoLongerAuthenticatedException extends Exception
    {
        /**
         * @var ActiveSession|null
         */
        private ?ActiveSession $activeSession;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * SessionNoLongerAuthenticatedException constructor.
         * @param string $message
         * @param ActiveSession|null $activeSession
         * @param Throwable|null $previous
         */
        public function __construct($message = "", ActiveSession $activeSession=null, Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::SessionNoLongerAuthenticatedException, $previous);
            $this->message = $message;
            $this->activeSession = $activeSession;
            $this->previous = $previous;
        }

        /**
         * @return ActiveSession|null
         */
        public function getActiveSession(): ?ActiveSession
        {
            return $this->activeSession;
        }
    }
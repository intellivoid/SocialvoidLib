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
    use SocialvoidLib\NetworkSession;
    use Throwable;

    /**
     * Class AlreadyAuthenticatedToNetwork
     * @package SocialvoidLib\Exceptions\Internal
     */
    class AlreadyAuthenticatedToNetwork extends Exception
    {
        /**
         * @var NetworkSession|null
         */
        private ?NetworkSession $network;


        /**
         * AlreadyAuthenticatedToNetwork constructor.
         * @param string $message
         * @param NetworkSession|null $network
         * @param Throwable|null $previous
         */
        public function __construct($message = "", NetworkSession $network=null, Throwable $previous = null)
        {
            parent::__construct($message, InternalErrorCodes::AlreadyAuthenticatedToNetwork, $previous);
            $this->message = $message;
            $this->network = $network;
        }

        /**
         * @return NetworkSession|null
         */
        public function getNetwork(): ?NetworkSession
        {
            return $this->network;
        }
    }
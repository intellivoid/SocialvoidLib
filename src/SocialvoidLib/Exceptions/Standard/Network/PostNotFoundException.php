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

    namespace SocialvoidLib\Exceptions\Standard\Network;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class PostNotFoundException
     * @package SocialvoidLib\Exceptions\Standard\Network
     */
    class PostNotFoundException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * PostNotFoundException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The requested post was not found", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::PostNotFoundException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
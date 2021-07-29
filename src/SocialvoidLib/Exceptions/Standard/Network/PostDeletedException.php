<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */
    /** @noinspection PhpMissingFieldTypeInspection */

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

    /**
     * Class PostDeletedException
     * @package SocialvoidLib\Exceptions\Standard\Network
     */
    class PostDeletedException extends Exception
    {
        /**
         * @var null
         */
        private $previous;

        /**
         * PostDeletedException constructor.
         * @param string $message
         * @param null $previous
         */
        public function __construct($message = "The requested post was deleted", $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::PostDeletedException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
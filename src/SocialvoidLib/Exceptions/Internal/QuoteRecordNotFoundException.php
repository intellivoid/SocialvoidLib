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
    use Throwable;

    /**
     * Class QuoteRecordNotFoundException
     * @package SocialvoidLib\Exceptions\Internal
     */
    class QuoteRecordNotFoundException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * QuoteRecordNotFoundException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, InternalErrorCodes::QuoteRecordNotFoundException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }
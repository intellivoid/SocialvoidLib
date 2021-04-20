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
     * Class CdnFileNotFoundException
     * @package SocialvoidLib\Exceptions\Internal
     */
    class CdnFileNotFoundException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * @var string|null
         */
        private ?string $public_id;

        /**
         * CdnFileNotFoundException constructor.
         * @param string $message
         * @param string|null $public_id
         * @param Throwable|null $previous
         */
        public function __construct($message = "", ?string $public_id=null, Throwable $previous = null)
        {
            parent::__construct($message, InternalErrorCodes::CdnFileNotFoundException, $previous);
            $this->message = $message;
            $this->previous = $previous;
            $this->public_id = $public_id;
        }

        /**
         * @return string|null
         */
        public function getPublicId(): ?string
        {
            return $this->public_id;
        }
    }
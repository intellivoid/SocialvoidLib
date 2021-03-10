<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

namespace SocialvoidLib\Exceptions\Standard\Validation;


    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class InvalidBiographyException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidBiographyException extends Exception
    {
        /**
         * @var string|null
         */
        private ?string $biography;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * InvalidBiographyException constructor.
         * @param string $message
         * @param string|null $biography
         * @param Throwable|null $previous
         */
        public function __construct($message = "", string $biography=null, Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidBiographyException, $previous);
            $this->message = $message;
            $this->biography = $biography;
            $this->previous = $previous;
        }
    }
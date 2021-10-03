<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

namespace SocialvoidLib\Exceptions\GenericInternal;


    use Exception;
    use Throwable;

    /**
     * Class InvalidSearchMethodException
     * @package SocialvoidLib\Exceptions\GenericInternal
     */
    class InvalidSearchMethodException extends Exception
    {
        /**
         * @var string
         */
        private string $search_method;

        /**
         * @var string
         */
        private string $value;


        /**
         * InvalidSearchMethodException constructor.
         * @param string $message
         * @param string $search_method
         * @param string $value
         * @param Throwable|null $previous
         */
        public function __construct($message = "", string $search_method="", string $value="", Throwable $previous = null)
        {
            parent::__construct($message, 0, $previous);
            $this->message = $message;
            $this->search_method = $search_method;
            $this->value = $value;
        }

        /**
         * @return string
         */
        public function getSearchMethod(): string
        {
            return $this->search_method;
        }

        /**
         * @return string
         */
        public function getValue(): string
        {
            return $this->value;
        }
    }
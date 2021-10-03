<?php
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
     * Class PeerNotFoundException
     * @package SocialvoidLib\Exceptions\Standard\Network
     */
    class PeerNotFoundException extends Exception
    {
        /**
         * @var string|null
         */
        private ?string $search_by;

        /**
         * @var string|null
         */
        private ?string $search_value;


        /**
         * PeerNotFoundException constructor.
         * @param string $message
         * @param string|null $search_by
         * @param string|null $search_value
         * @param Throwable|null $previous
         */
        public function __construct($message = "The requested peer was not found in the network", string $search_by=null, string $search_value=null, Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::PeerNotFoundException, $previous);
            $this->message = $message;
            $this->search_by = $search_by;
            $this->search_value = $search_value;
        }

        /**
         * @return string|null
         */
        public function getSearchBy(): ?string
        {
            return $this->search_by;
        }

        /**
         * @return string|null
         */
        public function getSearchValue(): ?string
        {
            return $this->search_value;
        }
    }
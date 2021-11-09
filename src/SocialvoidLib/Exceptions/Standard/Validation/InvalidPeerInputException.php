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
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Definitions\ErrorDefinition;
    use Throwable;

    /**
     * Class InvalidPeerInputException
     * @package SocialvoidLib\Exceptions\Standard\Network
     */
    class InvalidPeerInputException extends Exception implements StandardErrorInterface
    {
        /**
         * @var mixed
         */
        private $peer;


        /**
         * InvalidPeerInputException constructor.
         * @param string $message
         * @param null $peer
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given peer input is invalid", $peer=null, Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidPeerInputException, $previous);
            $this->message = $message;
            $this->peer = $peer;
        }

        /**
         * @return mixed
         */
        public function getPeer()
        {
            return $this->peer;
        }

        /**
         * @inheritDoc
         */
        public static function getDefinition(): ErrorDefinition
        {
            return new ErrorDefinition(self::getName(), self::getDescription(), self::getErrorCode());
        }

        /**
         * @inheritDoc
         */
        public static function getName(): string
        {
            return 'InvalidPeerInput';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The client provided an invalid peer identification as input';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::InvalidPeerInputException;
        }
    }
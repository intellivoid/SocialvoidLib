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
    use SocialvoidLib\Objects\Standard\ErrorDefinition;
    use Throwable;

    /**
     * Class UsernameAlreadyExistsException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class UsernameAlreadyExistsException extends Exception implements StandardErrorInterface
    {
        /**
         * @var string
         */
        private string $username;


        /**
         * UsernameAlreadyExistsException constructor.
         * @param string $message
         * @param string $username
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given username is already used on the network", string $username="", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::UsernameAlreadyExistsException, $previous);
            $this->message = $message;
            $this->username = $username;
        }

        /**
         * @return string
         */
        public function getUsername(): string
        {
            return $this->username;
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
            return 'UsernameAlreadyExists';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The username is already registered in the network and cannot be used';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::UsernameAlreadyExistsException;
        }
    }
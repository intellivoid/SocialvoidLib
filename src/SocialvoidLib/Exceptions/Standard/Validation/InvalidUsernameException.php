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

    namespace SocialvoidLib\Exceptions\Standard\Validation;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Definitions\ErrorDefinition;
    use Throwable;

    /**
     * Class InvalidUsernameException
     * @package SocialvoidLib\Exceptions\Standard
     */
    class InvalidUsernameException extends Exception implements StandardErrorInterface
    {
        /**
         * @var string|null|mixed
         */
        private $username;


        /**
         * InvalidUsernameException constructor.
         * @param string $message
         * @param string $username
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given username is invalid", $username="", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidUsernameException, $previous);

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
            return 'InvalidUsername';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The given username is invalid and does not meet the specification';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::InvalidUsernameException;
        }
    }
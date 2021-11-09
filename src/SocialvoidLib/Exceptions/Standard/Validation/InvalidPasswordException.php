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
     * Class InvalidPasswordException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidPasswordException extends Exception implements StandardErrorInterface
    {
        /**
         * @var string
         */
        private string $password;


        /**
         * InvalidPasswordException constructor.
         * @param string $message
         * @param string $password
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given password is invalid", $password="", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidPasswordException, $previous);
            $this->message = $message;
            $this->password = $password;
        }

        /**
         * @return string
         */
        public function getPassword(): string
        {
            return $this->password;
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
            return 'InvalidPassword';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The given password is insecure, see the message for further details';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::InvalidPasswordException;
        }
    }
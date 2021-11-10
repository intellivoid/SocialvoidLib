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
     * Class InvalidLastNameException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidLastNameException extends Exception implements StandardErrorInterface
    {
        /**
         * @var string|null
         */
        private ?string $last_name;


        /**
         * InvalidLastNameException constructor.
         * @param string $message
         * @param string|null $last_name
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given last name is invalid", string $last_name=null, Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidLastNameException, $previous);
            $this->message = $message;
            $this->last_name = $last_name;
        }

        /**
         * @return string|null
         */
        public function getLastName(): ?string
        {
            return $this->last_name;
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
            return 'InvalidLastName';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The Last Name provided contains invalid characters and or is too long, see the message for further details';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::InvalidLastNameException;
        }
    }
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
     * Class InvalidFirstNameException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidFirstNameException extends Exception implements StandardErrorInterface
    {
        /**
         * @var string|null
         */
        private ?string $first_name;

        /**
         * InvalidFirstNameException constructor.
         * @param string $message
         * @param string|null $first_name
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given first name is invalid", string $first_name=null, Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidFirstNameException, $previous);
            $this->message = $message;
            $this->first_name = $first_name;
        }

        /**
         * @return string|null
         */
        public function getFirstName(): ?string
        {
            return $this->first_name;
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
            return 'InvalidFirstName';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The First Name provided contains invalid characters and or is too long, see the message for further details';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::InvalidFirstNameException;
        }
    }
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
     * Class InvalidBiographyException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidBiographyException extends Exception implements StandardErrorInterface
    {
        /**
         * @var string|null
         */
        private ?string $biography;

        /**
         * InvalidBiographyException constructor.
         * @param string $message
         * @param string|null $biography
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given biography is invalid", string $biography=null, Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidBiographyException, $previous);
            $this->message = $message;
            $this->biography = $biography;
        }

        /**
         * @return string|null
         */
        public function getBiography(): ?string
        {
            return $this->biography;
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
            return 'InvalidBiography';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The Biography is too long or contains invalid characters, see the message for further details';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::InvalidBiographyException;
        }
    }
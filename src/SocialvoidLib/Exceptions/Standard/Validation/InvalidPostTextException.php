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
     * Class InvalidPostTextException
     * @package SocialvoidLib\Exceptions\Standard\Validation
     */
    class InvalidPostTextException extends Exception implements StandardErrorInterface
    {
        /**
         * @var string|null
         */
        private ?string $text;

        /**
         * InvalidPostTextException constructor.
         * @param string $message
         * @param string|null $text
         * @param Throwable|null $previous
         */
        public function __construct($message = "The given post text is invalid", string $text=null, Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::InvalidPostTextException, $previous);
            $this->message = $message;
            $this->text = $text;
        }

        /**
         * @return string|null
         */
        public function getText(): ?string
        {
            return $this->text;
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
            return 'InvalidPostText';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The post contains invalid characters or is too long, see the message for further details';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::InvalidPostTextException;
        }
    }
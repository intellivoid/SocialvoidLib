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

    namespace SocialvoidLib\Exceptions\Standard\Authentication;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Standard\ErrorDefinition;
    use Throwable;

    /**
     * Class AuthenticationNotApplicableException
     * @package SocialvoidLib\Exceptions\Standard\Authentication
     */
    class AuthenticationNotApplicableException extends Exception implements StandardErrorInterface
    {

        /**
         * AuthenticationNotApplicableException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "This authentication method is not applicable to the entity", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::AuthenticationNotApplicableException, $previous);
            $this->message = $message;
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
            return 'AuthenticationNotApplicable';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'Raised when the user does not support this method of authentication, see the message for further details';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::AuthenticationNotApplicableException;
        }
    }
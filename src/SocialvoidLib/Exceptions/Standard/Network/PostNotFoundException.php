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

    namespace SocialvoidLib\Exceptions\Standard\Network;

    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use SocialvoidLib\Interfaces\StandardErrorInterface;
    use SocialvoidLib\Objects\Definitions\ErrorDefinition;
    use Throwable;

    /**
     * Class PostNotFoundException
     * @package SocialvoidLib\Exceptions\Standard\Network
     */
    class PostNotFoundException extends Exception implements StandardErrorInterface
    {

        /**
         * PostNotFoundException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The requested post was not found", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::PostNotFoundException, $previous);
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
            return 'PostNotFound';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'Raised when the client requested a post that isn\'t found';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::PostNotFoundException;
        }
    }
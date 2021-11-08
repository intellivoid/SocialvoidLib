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
     * Class AlreadyRepostedException
     * @package SocialvoidLib\Exceptions\Standard\Network
     */
    class AlreadyRepostedException extends Exception implements StandardErrorInterface
    {

        /**
         * AlreadyRepostedException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "The post was already reposted", Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::AlreadyRepostedException, $previous);
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
            return 'AlreadyReposted';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'Raised when the client attempts to repost a post that has already been reposted';
        }

        /**
         * @inheritDoc
         */
        public static function getErrorCode(): int
        {
            return StandardErrorCodes::AlreadyRepostedException;
        }
    }
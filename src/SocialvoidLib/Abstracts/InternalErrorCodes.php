<?php

    namespace SocialvoidLib\Abstracts;

    use SocialvoidLib\Exceptions\Internal\RecoveryCodesAlreadyExistsException;
    use SocialvoidLib\Exceptions\Internal\TimeBasedPrivateSignatureAlreadyExistsException;

    /**
     * Internal Errors (Library related) starts with 2 as the hex divider.
     *
     * Class InternalErrorCodes
     * @package SocialvoidLib\Abstracts
     */
    abstract class InternalErrorCodes
    {
        /**
         * @see RecoveryCodesAlreadyExistsException
         */
        const RecoveryCodesAlreadyExistsException = 0x2000;

        /**
         * @see TimeBasedPrivateSignatureAlreadyExistsException
         */
        const TimeBasedPrivateSignatureAlreadyExistsException = 0x2001;
    }
<?php

    namespace SocialvoidLib\Abstracts;

    use SocialvoidLib\Exceptions\Internal\AuthenticationFailureException;
    use SocialvoidLib\Exceptions\Internal\NoRecoveryCodesAvailableException;
    use SocialvoidLib\Exceptions\Internal\NoTimeBasedSignatureAvailableException;
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

        /**
         * @see NoRecoveryCodesAvailableException
         */
        const NoRecoveryCodesAvailableException = 0x2002;

        /**
         * @see NoTimeBasedSignatureAvailableException
         */
        const NoTimeBasedSignatureAvailableException = 0x2003;

        /**
         * @see AuthenticationFailureException
         */
        const AuthenticationFailureException = 0x2004;
    }
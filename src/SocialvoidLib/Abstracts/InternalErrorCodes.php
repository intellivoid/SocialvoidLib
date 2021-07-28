<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Abstracts;

    use SocialvoidLib\Exceptions\Internal\AlreadyAuthenticatedToNetwork;
    use SocialvoidLib\Exceptions\Internal\AuthenticationFailureException;
    use SocialvoidLib\Exceptions\Internal\CdnFileNotFoundException;
    use SocialvoidLib\Exceptions\Internal\CoaAuthenticationRecordNotFoundException;
    use SocialvoidLib\Exceptions\Internal\EntityWithoutAccessException;
    use SocialvoidLib\Exceptions\Internal\FileTooLargeException;
    use SocialvoidLib\Exceptions\Internal\FollowerDataNotFound;
    use SocialvoidLib\Exceptions\Internal\FollowerStateNotFoundException;
    use SocialvoidLib\Exceptions\Internal\InvalidImageTypeException;
    use SocialvoidLib\Exceptions\Internal\LikeRecordNotFoundException;
    use SocialvoidLib\Exceptions\Internal\NoPasswordAuthenticationAvailableException;
    use SocialvoidLib\Exceptions\Internal\NoRecoveryCodesAvailableException;
    use SocialvoidLib\Exceptions\Internal\NoTimeBasedSignatureAvailableException;
    use SocialvoidLib\Exceptions\Internal\NoTwoFactorAuthenticationAvailableException;
    use SocialvoidLib\Exceptions\Internal\QuoteRecordNotFoundException;
    use SocialvoidLib\Exceptions\Internal\RecoveryCodesAlreadyExistsException;
    use SocialvoidLib\Exceptions\Internal\ReplyRecordNotFoundException;
    use SocialvoidLib\Exceptions\Internal\RepostRecordNotFoundException;
    use SocialvoidLib\Exceptions\Internal\TimeBasedPrivateSignatureAlreadyExistsException;
    use SocialvoidLib\Exceptions\Internal\TwoFactorAuthenticationRequiredException;
    use SocialvoidLib\Exceptions\Internal\UserTimelineNotFoundException;

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

        /**
         * @see AlreadyAuthenticatedToNetwork
         */
        const AlreadyAuthenticatedToNetwork = 0x2005;

        /**
         * @see FollowerDataNotFound
         */
        const FollowerDataNotFound = 0x2006;

        /**
         * @see FollowerStateNotFoundException
         */
        const FollowerStateNotFoundException = 0x2007;

        /**
         * @see LikeRecordNotFoundException
         */
        const LikeRecordNotFoundException = 0x2008;

        /**
         * @see RepostRecordNotFoundException
         */
        const RepostRecordNotFoundException = 0x2009;

        /**
         * @see UserTimelineNotFoundException
         */
        const UserTimelineNotFoundException = 0x2010;

        /**
         * @see QuoteRecordNotFoundException
         */
        const QuoteRecordNotFoundException = 0x2011;

        /**
         * @see CoaAuthenticationRecordNotFoundException
         */
        const CoaAuthenticationRecordNotFoundException = 0x2012;

        /**
         * @see ReplyRecordNotFoundException
         */
        const ReplyRecordNotFoundException = 0x2013;

        /**
         * @see FileTooLargeException
         */
        const FileTooLargeException = 0x2014;

        /**
         * @see CdnFileNotFoundException
         */
        const CdnFileNotFoundException = 0x2015;

        /**
         * @see InvalidImageTypeException
         */
        const InvalidImageTypeException = 0x2016;

        /**
         * @see EntityWithoutAccessException
         */
        const EntityWithoutAccessException = 0x2017;

        /**
         * @see NoTwoFactorAuthenticationAvailableException
         */
        const NoTwoFactorAuthenticationAvailableException = 0x2018;

        /**
         * @see NoPasswordAuthenticationAvailableException
         */
        const NoPasswordAuthenticationAvailableException = 0x2019;
    }
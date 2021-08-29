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

    use SocialvoidLib\Exceptions\Standard\Authentication\AccountNotRegisteredException;
    use SocialvoidLib\Exceptions\Standard\Authentication\AlreadyAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Authentication\AuthenticationNotApplicableException;
    use SocialvoidLib\Exceptions\Standard\Authentication\BadSessionChallengeAnswerException;
    use SocialvoidLib\Exceptions\Standard\Authentication\IncorrectLoginCredentialsException;
    use SocialvoidLib\Exceptions\Standard\Authentication\IncorrectTwoFactorAuthenticationCodeException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Authentication\PrivateAccessTokenRequiredException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionExpiredException;
    use SocialvoidLib\Exceptions\Standard\Authentication\TwoFactorAuthenticationRequiredException;
    use SocialvoidLib\Exceptions\Standard\Media\InvalidImageDimensionsException;
    use SocialvoidLib\Exceptions\Standard\Network\AccessDeniedException;
    use SocialvoidLib\Exceptions\Standard\Network\AlreadyRepostedException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\FileUploadException;
    use SocialvoidLib\Exceptions\Standard\Network\PostDeletedException;
    use SocialvoidLib\Exceptions\Standard\Network\PostNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Server\DocumentUploadException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidBiographyException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPrivateHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPublicHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidFirstNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidLastNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPasswordException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPeerInputException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPlatformException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPostTextException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidSessionIdentificationException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidUsernameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidVersionException;
    use SocialvoidLib\Exceptions\Standard\Validation\UsernameAlreadyExistsException;

    /**
     * Class StandardErrorCodes
     * @package SocialvoidLib\Abstracts
     */
    abstract class StandardErrorCodes
    {
        /** 21-Set Error codes (Validation) */

        /**
         * Raised when the the given username is invalid and does not match the standard rules
         *
         * @see InvalidUsernameException
         */
        const InvalidUsernameException = 0x02100;

        /**
         * Raised when the given password is invalid or not secured.
         *
         * @see InvalidPasswordException
         */
        const InvalidPasswordException = 0x02101;

        /**
         * @see InvalidFirstNameException
         */
        const InvalidFirstNameException = 0x02102;

        /**
         * @see InvalidLastNameException
         */
        const InvalidLastNameException = 0x02103;

        /**
         * @see InvalidBiographyException
         */
        const InvalidBiographyException = 0x02104;

        /**
         * @see UsernameAlreadyExistsException
         */
        const UsernameAlreadyExistsException = 0x02105;

        /**
         * Raised when the client provided a invalid peer identification input
         *
         * @see InvalidPeerInputException
         */
        const InvalidPeerInputException = 0x02106;

        /**
         * Raised when the post text given is invalid
         *
         * @see InvalidPostTextException
         */
        const InvalidPostTextException = 0x02107;

        /**
         * Raised when the client public hash is invalid
         *
         * @see InvalidClientPublicHashException
         */
        const InvalidClientPublicHashException = 0x02108;

        /**
         * Raised when the client private hash is invalid
         *
         * @see InvalidClientPrivateHashException
         */
        const InvalidClientPrivateHashException = 0x02109;

        /**
         * Raised when the given platform value is invalid
         *
         * @see InvalidPlatformException
         */
        const InvalidPlatformException = 0x02110;

        /**
         * Raised when the given version is invalid
         *
         * @see InvalidVersionException
         */
        const InvalidVersionException = 0x0210b;

        /**
         * Raised when the given client name is invalid
         *
         * @see InvalidClientNameException
         */
        const InvalidClientNameException = 0x0210c;

        /**
         * Raised when the given session identification object is invalid
         *
         * @see InvalidSessionIdentificationException
         */
        const InvalidSessionIdentificationException = 0x0210d;

        /** 22-Set error codes (Authentication) */

        /**
         * Raised when the given username or password is incorrect
         *
         * @see IncorrectLoginCredentialsException
         */
        const IncorrectLoginCredentialsException = 0x02200;

        /**
         * Raised when the given two factor authentication code is invalid (Recovery code or OTP)
         *
         * @see IncorrectTwoFactorAuthenticationCodeException
         */
        const IncorrectTwoFactorAuthenticationCodeException = 0x02201;

        /**
         * Raised when the simple authentication method is not applicable to this user
         *
         * @see AuthenticationNotApplicableException
         */
        const AuthenticationNotApplicableException = 0x02202;

        /**
         * Raised when the request session entity was not found on the network
         *
         * @see SessionNotFoundException
         */
        const SessionNotFoundException = 0x02203;

        /**
         * Raised when the user attempts to preform an action that requires authentication
         *
         * @see NotAuthenticatedException
         */
        const NotAuthenticatedException = 0x02204;

        /**
         * This user uses a private access token to authenticate rather than a traditional method
         *
         * @see PrivateAccessTokenRequiredException
         */
        const PrivateAccessTokenRequiredException = 0x02205;

        /**
         * If an internal server error occurs while trying to process the authentication
         *
         * @see AuthenticationFailureException
         */
        const AuthenticationFailureException = 0x02206;
        /**
         * Raised when the client gives a bad session challenge answer
         *
         * @see BadSessionChallengeAnswerException
         */
        const BadSessionChallengeAnswerException = 0x02207;

        /**
         * Raised when the client fails to provide two-factor authentication when required
         *
         * @see TwoFactorAuthenticationRequiredException
         */
        const TwoFactorAuthenticationRequiredException = 0x02208;

        /**
         * Raised when the client is already authenticated to the session
         *
         * @see AlreadyAuthenticatedException
         */
        const AlreadyAuthenticatedException = 0x02209;

        /**
         * Raised when the session has expired
         *
         * @see SessionExpiredException
         */
        const SessionExpiredException = 0x0220a;


        /** 23-Set error codes (Media) */

        /**
         * Raised when the given image type is not supported
         *
         * @see InvalidImageTypeException
         */
        const InvalidImageTypeException = 0x02300;

        /**
         * Raised when the given password is incorrect
         *
         * @see InvalidImageDimensionsException
         */
        const InvalidImageDimensionsException = 0x02301;


        /** 31-Set error codes (Network) */

        /**
         * Raised when the requested user entity was not found
         *
         * @see PeerNotFoundException
         */
        const PeerNotFoundException = 0x03100;

        /**
         * Raised when the client requested a post that isn't found
         *
         * @see PostNotFoundException
         */
        const PostNotFoundException = 0x03101;

        /**
         * Raised when the client requested a post that was deleted
         *
         * @see PostDeletedException
         */
        const PostDeletedException = 0x03102;

        /**
         * Raised when the client attempts to repost a post that has already been reposted
         *
         * @see AlreadyRepostedException
         */
        const AlreadyRepostedException = 0x03103;

        /**
         * Raised when there was an error while trying to upload one or more files to the network
         *
         * @see FileUploadException
         */
        const FileUploadErrorException = 0x03104;

        /**
         * Raised when the requested document ID was not found
         *
         * @see DocumentNotFoundException
         */
        const DocumentNotFoundException = 0x03105;

        /**
         * Raised when the requested resource is not accessible to the user
         *
         * @see AccessDeniedException
         */
        const AccessDeniedException = 0x03106;


        /** 40-Set error codes (Server) */

        /**
         * Raised when there was an unexpected server-side error.
         *
         * @see InternalServerException
         */
        const InternalServerError = 0x04000;

        /**
         * Raised when there was an error while trying to process the document upload
         *
         * @see DocumentUploadException
         */
        const DocumentUploadException = 0x04001;
    }
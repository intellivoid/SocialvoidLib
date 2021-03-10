<?php


    namespace SocialvoidLib\Abstracts;

    use SocialvoidLib\Exceptions\Standard\Authentication\AuthenticationNotApplicableException;
    use SocialvoidLib\Exceptions\Standard\Authentication\IncorrectPasswordException;
    use SocialvoidLib\Exceptions\Standard\Authentication\IncorrectTwoFactorAuthenticationCodeException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NoPasswordAuthenticationAvailableException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NoTwoFactorAuthenticationAvailableException;
    use SocialvoidLib\Exceptions\Standard\Network\PostNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidBiographyException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidFirstNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidLastNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPasswordException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPeerInputException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPostTextException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidUsernameException;
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


        /** 22-Set error codes (Authentication) */

        /**
         * Raised when the given password is incorrect
         *
         * @see IncorrectPasswordException
         */
        const IncorrectPasswordException = 0x02200;

        /**
         * Raised when the user has no password authentication method
         *
         * @see NoPasswordAuthenticationAvailableException
         */
        const NoPasswordAuthenticationAvailableException = 0x02201;

        /**
         * Raised when the given two factor authentication code is invalid (Recovery code or OTP)
         *
         * @see IncorrectTwoFactorAuthenticationCodeException
         */
        const IncorrectTwoFactorAuthenticationCodeException = 0x02202;

        /**
         * Raised when there is no two factor authentication code available for the user
         *
         * @see NoTwoFactorAuthenticationAvailableException
         */
        const NoTwoFactorAuthenticationAvailableException = 0x02203;

        /**
         * Raised when the simple authentication method is not applicable to this user
         *
         * @see AuthenticationNotApplicableException
         */
        const AuthenticationNotApplicableException = 0x02204;

        /**
         * Raised when the requested session is no longer authenticated
         *
         * @see SessionNoLongerAuthenticatedException
         */
        const SessionNoLongerAuthenticatedException = 0x02205;

        /**
         * Raised when the request session entity was not found on the network
         *
         * @see SessionNotFoundException
         */
        const SessionNotFoundException = 0x02206;

        /**
         * Raised when the user attempts to preform an action that requires authentication
         *
         * @see NotAuthenticatedException
         */
        const NotAuthenticatedException = 0x02207;


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
    }
<?php


    namespace SocialvoidLib\Abstracts;

    use SocialvoidLib\Exceptions\Standard\Authentication\AuthenticationNotApplicableException;
    use SocialvoidLib\Exceptions\Standard\Authentication\IncorrectPasswordException;
    use SocialvoidLib\Exceptions\Standard\Authentication\IncorrectTwoFactorAuthenticationCodeException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NoPasswordAuthenticationAvailableException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NoTwoFactorAuthenticationAvailableException;
    use SocialvoidLib\Exceptions\Standard\Network\SessionNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\UserNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidBiographyException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidFirstNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidLastNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPasswordException;
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



        /** 31-Set error codes (Network) */

        /**
         * Raised when the requested user entity was not found
         *
         * @see UserNotFoundException
         */
        const UserNotFoundException = 0x03100;

        /**
         * Raised when the request session entity was not found on the network
         *
         * @see SessionNotFoundException
         */
        const SessionNotFoundException = 0x03101;
    }
<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    /** @noinspection PhpMissingFieldTypeInspection */
    /** @noinspection PhpUnused */

    namespace SocialvoidLib\Objects\Peer;

    use Exception;
    use SocialvoidLib\Classes\Security\Hashing;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\Internal\NoPasswordAuthenticationAvailableException;
    use SocialvoidLib\Exceptions\Internal\NoRecoveryCodesAvailableException;
    use SocialvoidLib\Exceptions\Internal\NoTimeBasedSignatureAvailableException;
    use SocialvoidLib\Exceptions\Internal\NoTwoFactorAuthenticationAvailableException;
    use SocialvoidLib\Exceptions\Internal\RecoveryCodesAlreadyExistsException;
    use SocialvoidLib\Exceptions\Internal\TimeBasedPrivateSignatureAlreadyExistsException;
    use SocialvoidLib\Exceptions\Standard\Authentication\IncorrectLoginCredentialsException;
    use SocialvoidLib\Exceptions\Standard\Authentication\IncorrectTwoFactorAuthenticationCodeException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPasswordException;
    use tsa\Classes\Crypto;
    use tsa\Exceptions\BadLengthException;
    use tsa\Exceptions\InvalidSecretException;
    use tsa\Exceptions\SecuredRandomProcessorNotFoundException;

    /**
     * Class UserAuthenticationProperties
     * @package SocialvoidLib\Objects\User
     */
    class UserAuthenticationProperties
    {

        // TODO: Make recovery codes hashed, and compare the hash against the user input generated hash

        /**
         * The password storage string for validation
         *
         * @var string|null
         */
        public $Password;

        /**
         * The Unix Timestamp for when the password was last updated
         *
         * @var int|null
         */
        public $PasswordLastUpdated;

        /**
         * The time based private signature used to provide Two-Factor
         * Authentication to the user's account.
         *
         * @var string|null
         */
        public $TimeBasedPrivateSignature;

        /**
         * The Unix Timestamp for when this time based private signature was last updated
         *
         * @var int|null
         */
        public $TimeBasedPrivateSignatureLastUpdated;

        /**
         * An array of 12 recovery codes used as an alternative to
         * two-factor authentication if the time based private
         * signature is no longer possible
         *
         * @var string[]|null
         */
        public $RecoveryCodes;

        /**
         * The Unix Timestamp for when the recovery codes set was last updated
         *
         * @var int|null
         */
        public $RecoveryCodesLastUpdated;

        /**
         * Generates a new set of recovery codes, if a set already exists
         * while 'override' is set to False, an exception will be thrown
         *
         * Returns the recovery codes once successful
         *
         * @param bool $override
         * @return array
         * @throws BadLengthException
         * @throws SecuredRandomProcessorNotFoundException
         * @throws RecoveryCodesAlreadyExistsException
         * @noinspection PhpConditionCheckedByNextConditionInspection
         */
        public function generateRecoveryCodes(bool $override=False): array
        {
            if($override)
            {
                // Clear the current recovery codes if it's set to override
                $this->RecoveryCodes = [];
            }

            if($this->RecoveryCodes !== null && is_array($this->RecoveryCodes) && count($this->RecoveryCodes) > 0)
            {
                throw new RecoveryCodesAlreadyExistsException(
                    "There is already a set of recovery codes that exists with this user", $this);
            }

            // Generate 12 sets of recovery codes
            for ($i = 0; $i < 12; $i++)
                $this->RecoveryCodes[] = Hashing::generateRecoveryCode();
            $this->RecoveryCodesLastUpdated = time();

            return $this->RecoveryCodes;
        }

        /**
         * Validates a recovery code
         *
         * @param string $input
         * @param bool $throw_exception
         * @return bool
         * @throws NoRecoveryCodesAvailableException
         */
        public function validateRecoveryCode(string $input, bool $throw_exception=false): bool
        {
            if($this->RecoveryCodes == null)
            {
                if($throw_exception)
                    throw new NoRecoveryCodesAvailableException("Recovery codes are set to null", $this);
                return false;
            }

            if(is_array($this->RecoveryCodes) && count($this->RecoveryCodes) == 0)
            {
                if($throw_exception)
                    throw new NoRecoveryCodesAvailableException("There are no recovery codes left", $this);
                return false;
            }

            return in_array($input, $this->RecoveryCodes);
        }

        /**
         * Removes an existing recovery code
         *
         * @param string $input
         * @param bool $throw_exception
         * @throws NoRecoveryCodesAvailableException
         */
        public function removeRecoveryCode(string $input, bool $throw_exception=false): void
        {
            if($this->RecoveryCodes == null)
            {
                if($throw_exception)
                    throw new NoRecoveryCodesAvailableException("Recovery codes are set to null", $this);
                return;

            }

            if(is_array($this->RecoveryCodes) && count($this->RecoveryCodes) == 0)
            {
                if($throw_exception)
                    throw new NoRecoveryCodesAvailableException("There are no recovery codes left", $this);
                return;
            }

            $this->RecoveryCodes = array_diff($this->RecoveryCodes, [$input]);
        }

        /**
         * Disables recovery codes
         */
        public function disableRecoveryCodes(): void
        {
            $this->RecoveryCodes = null;
            $this->RecoveryCodesLastUpdated = time();
        }

        /**
         * Generates a new time based private signature, throws an exception
         * if one already exists and override isn't set to True
         *
         * @param bool $override
         * @return string
         * @throws BadLengthException
         * @throws SecuredRandomProcessorNotFoundException
         * @throws TimeBasedPrivateSignatureAlreadyExistsException
         */
        public function generateTimeBasedPrivateSignature(bool $override=False): string
        {
            if($override)
            {
                // Clear the current recovery codes if it's set to override
                $this->TimeBasedPrivateSignature = null;
            }

            if($this->RecoveryCodes !== null)
            {
                throw new TimeBasedPrivateSignatureAlreadyExistsException(
                    "There is a time based private signature with this user", $this);
            }

            $this->TimeBasedPrivateSignature = Crypto::BuildSecretSignature(32);
            $this->TimeBasedPrivateSignatureLastUpdated = time();

            return $this->TimeBasedPrivateSignature;
        }

        /**
         * Returns the current time based signature one-time password
         *
         * @return string
         * @throws NoTimeBasedSignatureAvailableException
         * @throws InvalidSecretException
         */
        public function currentTimeBasedOneTimePassword(): string
        {
            if($this->TimeBasedPrivateSignature == null)
                throw new NoTimeBasedSignatureAvailableException("There are no time based signatures available", $this);

            return Crypto::getCode($this->TimeBasedPrivateSignature);
        }

        /**
         * Validates the time based one time password input
         *
         * @param string $input
         * @return bool
         * @throws NoTimeBasedSignatureAvailableException
         */
        public function validateTimeBasedOneTimePassword(string $input): bool
        {
            if($this->TimeBasedPrivateSignature == null)
                throw new NoTimeBasedSignatureAvailableException("There are no time based signatures available", $this);

            try
            {
                if(Crypto::verifyCode($this->TimeBasedPrivateSignature, $input) == true)
                    return True;
            }
            catch(Exception $exception)
            {
                return False;
            }

            return False;
        }

        /**
         * Disables the time based private signature method
         */
        public function disableTimeBasedPrivateSignature(): void
        {
            $this->TimeBasedPrivateSignature = null;
            $this->TimeBasedPrivateSignatureLastUpdated = time();
        }

        /**
         * Sets the password property properly.
         *
         * @param string|null $password
         * @throws InvalidPasswordException
         */
        public function setPassword(?string $password): void
        {
            if(Validate::password($password) == false)
                throw new InvalidPasswordException("The given password is considered unsafe or invalid", $password);

            $this->Password = Hashing::password($password);
            $this->PasswordLastUpdated = time();
        }

        /**
         * Disables the simple password
         */
        public function disablePassword(): void
        {
            $this->Password = null;
            $this->PasswordLastUpdated = time();
        }

        /**
         * Validates the two-factor authentication code
         *
         * @param string $input
         * @param bool $update
         * @return bool
         * @throws IncorrectTwoFactorAuthenticationCodeException
         * @throws NoRecoveryCodesAvailableException
         * @throws NoTwoFactorAuthenticationAvailableException
         */
        public function twoFactorAuthentication(string $input, bool $update=true): bool
        {
            if($this->RecoveryCodes == null && $this->TimeBasedPrivateSignature == null)
                throw new NoTwoFactorAuthenticationAvailableException("No recovery codes or time based methods are configured");

            //  Try OTP
            if($this->TimeBasedPrivateSignature !== null)
            {
                try
                {
                    if($this->validateTimeBasedOneTimePassword($input))
                        return True;
                }
                catch(Exception $e)
                {
                    unset($e);
                }
            }

            // Try RecoveryCode
            if($this->RecoveryCodes !== null)
            {
                /** @noinspection PhpRedundantOptionalArgumentInspection */
                if($this->validateRecoveryCode($input, false))
                {
                    if($update)
                        /** @noinspection PhpRedundantOptionalArgumentInspection */
                        $this->removeRecoveryCode($input, false);
                    return true;
                }
            }

            throw new IncorrectTwoFactorAuthenticationCodeException("Two Factor authentication validation failed");
        }

        /**
         * Processes a password authentication method
         *
         * @param string $password
         * @return bool
         * @throws IncorrectLoginCredentialsException
         * @throws NoPasswordAuthenticationAvailableException
         * @throws InvalidPasswordException
         */
        public function passwordAuthentication(string $password): bool
        {
            if($this->Password == null)
                throw new NoPasswordAuthenticationAvailableException("No password authentication method has been configured");

            if(Validate::password($password) == false)
                throw new IncorrectLoginCredentialsException("The given password or username is incorrect");

            if(Hashing::password($password) !== $this->Password)
                throw new IncorrectLoginCredentialsException("The given password or username is incorrect");

            return true;
        }

        /**
         * Returns an array representation of this object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "password" => $this->Password,
                "password_last_updated" => $this->PasswordLastUpdated,
                "time_based_private_signature" => $this->TimeBasedPrivateSignature,
                "time_based_private_signature_last_updated" => $this->TimeBasedPrivateSignatureLastUpdated,
                "recovery_codes" => $this->RecoveryCodes,
                "recovery_codes_last_updated" => $this->RecoveryCodesLastUpdated,
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return UserAuthenticationProperties
         */
        public static function fromArray(array $data): UserAuthenticationProperties
        {
            $UserAuthenticationPropertiesObject = new UserAuthenticationProperties();

            if(isset($data["password"]))
                $UserAuthenticationPropertiesObject->Password = $data["password"];

            if(isset($data["password_last_updated"]))
                $UserAuthenticationPropertiesObject->PasswordLastUpdated = $data["password_last_updated"];

            if(isset($data["time_based_private_signature"]))
                $UserAuthenticationPropertiesObject->TimeBasedPrivateSignature = $data["time_based_private_signature"];

            if(isset($data["time_based_private_signature_last_updated"]))
                $UserAuthenticationPropertiesObject->TimeBasedPrivateSignatureLastUpdated = $data["time_based_private_signature_last_updated"];

            if(isset($data["recovery_codes"]))
                $UserAuthenticationPropertiesObject->RecoveryCodes = $data["recovery_codes"];

            if(isset($data["recovery_codes_last_updated"]))
                $UserAuthenticationPropertiesObject->RecoveryCodesLastUpdated = $data["recovery_codes_last_updated"];

            return $UserAuthenticationPropertiesObject;
        }

    }
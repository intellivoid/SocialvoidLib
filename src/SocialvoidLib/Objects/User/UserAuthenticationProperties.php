<?php

    /** @noinspection PhpMissingFieldTypeInspection */
    /** @noinspection PhpUnused */

    namespace SocialvoidLib\Objects\User;

    use SocialvoidLib\Classes\Security\Hashing;
    use SocialvoidLib\Exceptions\Internal\RecoveryCodesAlreadyExistsException;
    use SocialvoidLib\Exceptions\Internal\TimeBasedPrivateSignatureAlreadyExistsException;
    use tsa\Classes\Crypto;
    use tsa\Exceptions\BadLengthException;
    use tsa\Exceptions\SecuredRandomProcessorNotFoundException;

    /**
     * Class UserAuthenticationProperties
     * @package SocialvoidLib\Objects\User
     */
    class UserAuthenticationProperties
    {
        // TODO: Come up with a secured password storage implementation

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
        public $PasswordLastUpdatedTimestamp;

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
         * The ID of the Application used to authenticate the user
         *
         * @var string|null
         */
        public $CoaApplicationId;

        /**
         * @var string
         */
        public $CoaApplicationIdLastUpdatedTimestamp;

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
            $this->RecoveryCodesLastUpdated = (int)time();

            return $this->RecoveryCodes;
        }

        /**
         * Disables recovery codes
         */
        public function disableRecoveryCodes(): void
        {
            $this->RecoveryCodes = null;
            $this->RecoveryCodesLastUpdated = (int)time();
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
            $this->TimeBasedPrivateSignatureLastUpdated = (int)time();

            return $this->TimeBasedPrivateSignature;
        }

        /**
         * Disables the time based private signature method
         */
        public function disableTimeBasedPrivateSignature(): void
        {
            $this->TimeBasedPrivateSignature = null;
            $this->TimeBasedPrivateSignatureLastUpdated = (int)time();
        }

        /**
         * Sets the password property properly.
         *
         * @param string|null $password
         */
        public function setPassword(?string $password): void
        {
            $this->Password = Hashing::password($password);
            $this->PasswordLastUpdatedTimestamp = (int)time();
        }

        /**
         * Disables the simple password
         */
        public function disablePassword(): void
        {
            $this->Password = null;
            $this->PasswordLastUpdatedTimestamp = (int)time();
        }

        /**
         * Sets the COA Application ID used to authenticate the user
         *
         * @param string|null $application_id
         */
        public function setCoaApplicationId(?string $application_id): void
        {
            $this->CoaApplicationId = $application_id;
            $this->CoaApplicationIdLastUpdatedTimestamp = (int)time();
        }

        /**
         * Disables the COA Application ID
         */
        public function disableCoaApplicationId(): void
        {
            $this->CoaApplicationId = null;
            $this->CoaApplicationIdLastUpdatedTimestamp = (int)time();
        }

    }
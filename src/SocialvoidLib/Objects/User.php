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

    namespace SocialvoidLib\Objects;

    use Exception;
    use SocialvoidLib\Abstracts\Flags\UserFlags;
    use SocialvoidLib\Abstracts\StatusStates\UserPrivacyState;
    use SocialvoidLib\Abstracts\StatusStates\UserStatus;
    use SocialvoidLib\Abstracts\UserAuthenticationMethod;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\Internal\AuthenticationFailureException;
    use SocialvoidLib\Exceptions\Internal\NoPasswordAuthenticationAvailableException;
    use SocialvoidLib\Exceptions\Internal\NoRecoveryCodesAvailableException;
    use SocialvoidLib\Exceptions\Standard\Authentication\AuthenticationNotApplicableException;
    use SocialvoidLib\Exceptions\Standard\Authentication\IncorrectLoginCredentialsException;
    use SocialvoidLib\Exceptions\Standard\Authentication\IncorrectTwoFactorAuthenticationCodeException;
    use SocialvoidLib\Exceptions\Standard\Authentication\PrivateAccessTokenRequiredException;
    use SocialvoidLib\Exceptions\Standard\Authentication\TwoFactorAuthenticationRequiredException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPasswordException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidUsernameException;
    use SocialvoidLib\Objects\User\Profile;
    use SocialvoidLib\Objects\User\UserAuthenticationProperties;
    use SocialvoidLib\Objects\User\UserProperties;
    use SocialvoidLib\Objects\User\UserSettings;

    /**
     * Class User
     * @package SocialvoidLib\Objects
     */
    class User
    {
        /**
         * The Unique Internal Database ID for this user
         *
         * @var int
         */
        public $ID;

        /**
         * The Public ID for this user
         *
         * @var string
         */
        public $PublicID;

        /**
         * The username of the user (Capital preferences)
         *
         * @var string
         */
        public $Username;

        /**
         * The username of the user (Lowercase, safe preference for indexing)
         *
         * @var string
         */
        public $UsernameSafe;

        /**
         * The domain of the network that this user is from
         *
         * @var string
         */
        public $Network;

        /**
         * The current status of the user which indicates what
         * activities they can preform on the network
         *
         * @var UserStatus
         */
        public $Status;

        /**
         * The Unix Timestamp for when this user's status is
         * changed back to "Active"
         *
         * @var int
         */
        public $StatusChangeTimestamp;

        /**
         * Serializable set of properties attached to this user
         *
         * @var UserProperties
         */
        public $Properties;

        /**
         * An array of flags associated with this user
         *
         * @var UserFlags[]
         */
        public $Flags;

        /**
         * The authentication method used to authenticate to this
         * account using the designated method that is supported
         *
         * @var UserAuthenticationMethod|string
         */
        public $AuthenticationMethod;

        /**
         * The authentication properties used by the user
         *
         * @var UserAuthenticationProperties
         */
        public $AuthenticationProperties;

        /**
         * The private access token used to access the user without direct authentication
         *
         * @var string|null
         */
        public $PrivateAccessToken;

        /**
         * The profile data of the user
         *
         * @var Profile
         */
        public $Profile;

        /**
         * The display picture document for the user
         *
         * @var Document
         */
        public $DisplayPictureDocument;

        /**
         * The settings configuration of the user
         *
         * @var UserSettings
         */
        public $Settings;

        /**
         * The current user privacy state of the account
         *
         * @var UserPrivacyState|string
         */
        public $PrivacyState;

        /**
         * The slave server hash that this user has their data stored on
         *
         * @var string
         */
        public $SlaveServer;

        /**
         * The Unix Timestamp for when this user last interacted with the network
         *
         * @var int
         */
        public $LastActivityTimestamp;

        /**
         * The Unix Timestamp for when this user first registered into this network
         *
         * @var int
         */
        public $CreatedTimestamp;

        /**
         * User constructor.
         */
        public function __construct()
        {
            $this->Properties = new UserProperties();
            $this->Flags = [];
            $this->Properties = new UserProperties();
            $this->AuthenticationProperties = new UserAuthenticationProperties();
            $this->Profile = new Profile();
            $this->Settings = new UserSettings();
        }

        /**
         * Validates and sets a new username to this user.
         *
         * @param string $username
         * @throws InvalidUsernameException
         */
        public function setUsername(string $username): void
        {
            if(Validate::username($username) == false)
                throw new InvalidUsernameException("The given username isn't valid for the network standard", $username);

            $this->Username = $username;
            $this->UsernameSafe = strtolower($username);
        }

        /**
         * Performs a simple authentication against the user. Make sure to update the user after
         * executing this method
         *
         * @param string $password
         * @param string|null $otp
         * @param bool $ignore_otp
         * @param bool $update
         * @return bool
         * @throws AuthenticationFailureException
         * @throws AuthenticationNotApplicableException
         * @throws IncorrectLoginCredentialsException
         * @throws IncorrectTwoFactorAuthenticationCodeException
         * @throws NoPasswordAuthenticationAvailableException
         * @throws PrivateAccessTokenRequiredException
         * @throws TwoFactorAuthenticationRequiredException
         * @throws InvalidPasswordException
         */
        public function simpleAuthentication(string $password, string $otp=null, bool $ignore_otp=false, bool $update=true): bool
        {
            switch($this->AuthenticationMethod)
            {
                case UserAuthenticationMethod::Simple:
                    $this->AuthenticationProperties->passwordAuthentication($password);
                    break;

                case UserAuthenticationMethod::SimpleSecured:
                    if($otp == null && $ignore_otp == false)
                        throw new TwoFactorAuthenticationRequiredException("Two factor authentication is required");

                    $this->AuthenticationProperties->passwordAuthentication($password);

                    if($otp !== null)
                        try
                        {
                            $this->AuthenticationProperties->twoFactorAuthentication($otp, $update);
                        }
                        catch (IncorrectTwoFactorAuthenticationCodeException | NoRecoveryCodesAvailableException $e)
                        {
                            throw new IncorrectTwoFactorAuthenticationCodeException("The provided two factor authentication is incorrect", $e);
                        }
                        catch(Exception $e)
                        {
                            throw new AuthenticationFailureException("There was an unexpected error while trying to process the authentication", $e);
                        }

                    break;

                case UserAuthenticationMethod::None:
                    throw new AuthenticationNotApplicableException("The user has no traditional authentication method available");


                case UserAuthenticationMethod::PrivateAccessToken:
                    throw new PrivateAccessTokenRequiredException("The user uses a private access token to authenticate");

            }

            return true;
        }

        /**
         * Disables all authentication methods for this user
         */
        public function disableAllAuthenticationMethods(): void
        {
            $this->AuthenticationMethod = UserAuthenticationMethod::None;
            $this->AuthenticationProperties->disablePassword();
            $this->AuthenticationProperties->disableRecoveryCodes();
            $this->AuthenticationProperties->disableTimeBasedPrivateSignature();
        }

        /**
         * Returns the display name of the user
         *
         * @return string
         */
        public function getDisplayName(): string
        {
            if($this->Profile->LastName == null)
            {
                if($this->Profile->FirstName == null)
                {
                    return $this->Username;
                }

                return $this->Profile->FirstName;
            }

            return $this->Profile->FirstName . ' ' . $this->Profile->LastName;
        }

        /**
         * Returns an array representation of the user object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "id" => $this->ID,
                "public_id" => $this->PublicID,
                "username" => $this->Username,
                "username_safe" => $this->UsernameSafe,
                "network" => $this->Network,
                "status" => $this->Status,
                "status_change_timestamp" => $this->StatusChangeTimestamp,
                "properties" => $this->Properties->toArray(),
                "flags" => $this->Flags,
                "authentication_method" => $this->AuthenticationMethod,
                "authentication_properties" => $this->AuthenticationProperties->toArray(),
                "private_access_token" => $this->PrivateAccessToken,
                "profile" => $this->Profile->toArray(),
                "display_picture_document" => $this->DisplayPictureDocument->toArray(),
                "settings" => $this->Settings->toArray(),
                "privacy_state" => $this->PrivacyState,
                "slave_server" => $this->SlaveServer,
                "last_activity_timestamp" => $this->LastActivityTimestamp,
                "created_timestamp" => $this->CreatedTimestamp
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return User
         */
        public static function fromArray(array $data): User
        {
            $UserObject = new User();

            if(isset($data["id"]))
            {
                if($data["id"] !== null)
                    $UserObject->ID = (int)$data["id"];
            }

            if(isset($data["public_id"]))
                $UserObject->PublicID = $data["public_id"];

            if(isset($data["username"]))
                $UserObject->Username = $data["username"];

            if(isset($data["username_safe"]))
                $UserObject->UsernameSafe = $data["username_safe"];

            if(isset($data["network"]))
                $UserObject->Network = $data["network"];

            if(isset($data["status"]))
                $UserObject->Status = $data["status"];

            if(isset($data["status_change_timestamp"]))
            {
                if($data["status_change_timestamp"] !== null)
                {
                    $UserObject->StatusChangeTimestamp = (int)$data["status_change_timestamp"];
                }
            }

            if(isset($data["properties"]))
                $UserObject->Properties = UserProperties::fromArray($data["properties"]);

            if(isset($data["flags"]))
                $UserObject->Flags = $data["flags"];

            if(isset($data["authentication_method"]))
                $UserObject->AuthenticationMethod = $data["authentication_method"];

            if(isset($data["authentication_properties"]))
                $UserObject->AuthenticationProperties = UserAuthenticationProperties::fromArray($data["authentication_properties"]);

            if(isset($data["private_access_token"]))
                $UserObject->PrivateAccessToken = $data["private_access_token"];

            if(isset($data["profile"]))
                $UserObject->Profile = Profile::fromArray($data["profile"]);

            if(isset($data["display_picture_document"]))
                $UserObject->DisplayPictureDocument = Document::fromArray($data["display_picture_document"]);

            if(isset($data["settings"]))
                $UserObject->Settings = UserSettings::fromArray($data["settings"]);

            if(isset($data["privacy_state"]))
                $UserObject->PrivacyState = $data["privacy_state"];

            if(isset($data["slave_server"]))
                $UserObject->SlaveServer = $data["slave_server"];

            if(isset($data["last_activity_timestamp"]))
            {
                if($data["last_activity_timestamp"] !== null)
                    $UserObject->LastActivityTimestamp = (int)$data["last_activity_timestamp"];
            }

            if(isset($data["created_timestamp"]))
            {
                if($data["created_timestamp"] !== null)
                    $UserObject->CreatedTimestamp = (int)$data["created_timestamp"];
            }

            return $UserObject;
        }
    }
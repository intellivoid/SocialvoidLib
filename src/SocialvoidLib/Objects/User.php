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
    use SocialvoidLib\Exceptions\Standard\Authentication\AuthenticationNotApplicableException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidUsernameException;
    use SocialvoidLib\Objects\User\CoaUserEntity;
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
         * @var UserAuthenticationMethod
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
         * The COA User Entity data collected from the COA provider
         *
         * @var CoaUserEntity
         */
        public $CoaUserEntity;

        /**
         * The profile data of the user
         *
         * @var Profile
         */
        public $Profile;

        /**
         * The settings configuration of the user
         *
         * @var UserSettings
         */
        public $Settings;

        /**
         * The current user privacy state of the account
         *
         * @var UserPrivacyState
         */
        public $PrivacyState;

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
            $this->CoaUserEntity = new CoaUserEntity();
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
         */
        public function simpleAuthentication(string $password, string $otp=null, bool $ignore_otp=false, bool $update=true): bool
        {
            try
            {
                switch($this->AuthenticationMethod)
                {
                    case UserAuthenticationMethod::Simple:
                        $this->AuthenticationProperties->passwordAuthentication($password);
                        break;

                    case UserAuthenticationMethod::SimpleSecured:
                        if($otp == null && $ignore_otp == false)
                            throw new AuthenticationFailureException("Two factor authentication is required");

                        $this->AuthenticationProperties->passwordAuthentication($password);

                        if($otp !== null)
                            $this->AuthenticationProperties->twoFactorAuthentication($otp, $update);

                        break;

                    case UserAuthenticationMethod::None:
                        throw new AuthenticationNotApplicableException("The user has no authentication method available");

                    case UserAuthenticationMethod::CrossOverAuthentication:
                        throw new AuthenticationNotApplicableException("The user uses COA to authenticate");

                }
            }
            catch(Exception $e)
            {
                throw new AuthenticationFailureException("The provided credentials are invalid or incorrect", $e);
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
            $this->AuthenticationProperties->disableCoaApplicationId();
            $this->AuthenticationProperties->disableRecoveryCodes();
            $this->AuthenticationProperties->disableTimeBasedPrivateSignature();
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
                "coa_user_entity" => $this->CoaUserEntity->toArray(),
                "profile" => $this->Profile->toArray(),
                "settings" => $this->Settings->toArray(),
                "privacy_state" => $this->PrivacyState,
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

            if(isset($data["coa_user_entity"]))
                $UserObject->CoaUserEntity = CoaUserEntity::fromArray($data["coa_user_entity"]);

            if(isset($data["profile"]))
                $UserObject->Profile = Profile::fromArray($data["profile"]);

            if(isset($data["settings"]))
                $UserObject->Settings = UserSettings::fromArray($data["settings"]);

            if(isset($data["privacy_state"]))
                $UserObject->PrivacyState = $data["privacy_state"];

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
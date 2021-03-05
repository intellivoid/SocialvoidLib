<?php

    /** @noinspection PhpUnused */


    namespace SocialvoidLib\Managers;

    use Exception;
    use msqg\QueryBuilder;
    use mysqli;
    use SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod;
    use SocialvoidLib\Abstracts\StatusStates\UserPrivacyState;
    use SocialvoidLib\Abstracts\StatusStates\UserStatus;
    use SocialvoidLib\Abstracts\UserAuthenticationMethod;
    use SocialvoidLib\Classes\Converter;
    use SocialvoidLib\Classes\Standard\BaseIdentification;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Standard\Network\UserNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidFirstNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidLastNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidUsernameException;
    use SocialvoidLib\Exceptions\Standard\Validation\UsernameAlreadyExistsException;
    use SocialvoidLib\Objects\User;
    use ZiProto\ZiProto;

    /**
     * Class UserManager
     * @package SocialvoidLib\Managers
     */
    class UserManager
    {
        /**
         * The database connection
         *
         * @var mysqli
         */
        private mysqli $mysqli;

        /**
         * The network configuration ACM
         *
         * @var array
         */
        private array $network_configuration;

        /**
         * UserManager constructor.
         * @param mysqli $mysqli
         * @param array $network_configuration
         */
        public function __construct(mysqli $mysqli, array $network_configuration)
        {
            $this->mysqli = $mysqli;
            $this->network_configuration = $network_configuration;
        }

        /**
         * Registers a new user into the database, no authentication will be applied; this must be added
         * after the registration was successful.
         *
         * @param string $username
         * @param string $first_name
         * @param string|null $last_name
         * @return User
         * @throws DatabaseException
         * @throws InvalidFirstNameException
         * @throws InvalidLastNameException
         * @throws InvalidSearchMethodException
         * @throws InvalidUsernameException
         * @throws UserNotFoundException
         * @throws UsernameAlreadyExistsException
         */
        public function registerUser(string $username, string $first_name, string $last_name=null): User
        {
            if(Validate::username($username) == false)
                throw new InvalidUsernameException("The given username is invalid", $username);

            if(Validate::firstName($first_name) == false)
                throw new InvalidFirstNameException("The given first name is invalid or empty", $first_name);

            if(Validate::lastName($last_name) == false)
                throw new InvalidLastNameException("The given last name is invalid or empty", $last_name);

            if($this->checkUsernameExists($username))
                throw new UsernameAlreadyExistsException("The given username is already registered on the network", $username);

            $Profile = new User\Profile();
            $Profile->FirstName = Converter::emptyString($first_name);
            $Profile->LastName = Converter::emptyString($last_name);

            $UserProperties = new User\UserProperties();
            $UserAuthenticationProperties = new User\UserAuthenticationProperties();
            $CoaUserEntity = new User\CoaUserEntity();
            $Settings = new User\UserSettings();

            $timestamp = (int)time();
            $public_id = BaseIdentification::UserPublicID($timestamp);

            $Query = QueryBuilder::insert_into("users", [
                "public_id" => $this->mysqli->real_escape_string($public_id),
                "username" => $this->mysqli->real_escape_string($username),
                "username_safe" => $this->mysqli->real_escape_string(strtolower($username)),
                "network" => $this->mysqli->real_escape_string($this->network_configuration["Domain"]),
                "status" => $this->mysqli->real_escape_string(UserStatus::Active),
                "status_change_timestamp" => 0,
                "properties" => $this->mysqli->real_escape_string(ZiProto::encode($UserProperties->toArray())),
                "flags" => $this->mysqli->real_escape_string(ZiProto::encode([])),
                "authentication_method" => $this->mysqli->real_escape_string(UserAuthenticationMethod::None),
                "authentication_properties" => $this->mysqli->real_escape_string(ZiProto::encode($UserAuthenticationProperties->toArray())),
                "private_access_token" => null,
                "coa_user_entity" => $this->mysqli->real_escape_string(ZiProto::encode($CoaUserEntity->toArray())),
                "profile" => $this->mysqli->real_escape_string(ZiProto::encode($Profile->toArray())),
                "settings" => $this->mysqli->real_escape_string(ZiProto::encode($Settings->toArray())),
                "privacy_state" => $this->mysqli->real_escape_string(UserPrivacyState::Public),
                "last_activity_timestamp" => (int)$timestamp,
                "created_timestamp" => (int)$timestamp
            ]);

            $QueryResults = $this->mysqli->query($Query);

            if($QueryResults)
            {
                return $this->getUser(UserSearchMethod::ByPublicId, $public_id);
            }
            else
            {
                throw new DatabaseException("There was an error while trying to register the user",
                    $Query, $this->mysqli->error, $this->mysqli
                );
            }
        }

        /**
         * Returns an existing user from the database
         *
         * @param string|UserSearchMethod $search_method
         * @param string|int $value
         * @return User
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws UserNotFoundException
         */
        public function getUser(string $search_method, $value): User
        {
            switch($search_method)
            {
                case UserSearchMethod::ById:
                    $search_method = $this->mysqli->real_escape_string($search_method);
                    $value = (int)$value;
                    break;

                case UserSearchMethod::ByPublicId:
                    $search_method = $this->mysqli->real_escape_string($search_method);
                    $value = $this->mysqli->real_escape_string($value);
                    break;

                case UserSearchMethod::ByUsername:
                    $search_method = $this->mysqli->real_escape_string($search_method);
                    $value = $this->mysqli->real_escape_string(strtolower($value));
                    break;

                default:
                    throw new InvalidSearchMethodException(
                        "The search method '$search_method' is not applicable to getUser()",
                        $search_method, $value);
            }

            $Query = QueryBuilder::select("users", [
                "id",
                "public_id",
                "username",
                "username_safe",
                "network",
                "status",
                "status_change_timestamp",
                "properties",
                "flags",
                "authentication_method",
                "authentication_properties",
                "private_access_token",
                "coa_user_entity",
                "profile",
                "settings",
                "privacy_state",
                "last_activity_timestamp",
                "created_timestamp"
            ], $search_method, $value, null, null, 1);

            $QueryResults = $this->mysqli->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new UserNotFoundException();
                }
                else
                {
                    $Row["properties"] = ZiProto::decode($Row["properties"]);
                    $Row["flags"] = ZiProto::decode($Row["flags"]);
                    $Row["authentication_properties"] = ZiProto::decode($Row["authentication_properties"]);
                    $Row["coa_user_entity"] = ZiProto::decode($Row["coa_user_entity"]);
                    $Row["profile"] = ZiProto::decode($Row["profile"]);
                    $Row["settings"] = ZiProto::decode($Row["settings"]);

                    return(User::fromArray($Row));
                }
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying retrieve the user from the network",
                    $Query, $this->mysqli->error, $this->mysqli
                );
            }
        }

        /**
         * Updates an existing user in the network
         *
         * @param User $user
         * @return User
         * @throws DatabaseException
         */
        public function updateUser(User $user): User
        {
            $user->LastActivityTimestamp = (int)time();
            $Query = QueryBuilder::update("users", [
                "username" => $this->mysqli->real_escape_string($user->Username),
                "username_safe" => $this->mysqli->real_escape_string($user->UsernameSafe),
                "network" => $this->mysqli->real_escape_string($user->Network),
                "status" => ($user->StatusChangeTimestamp !== null ? (int)$user->StatusChangeTimestamp : null),
                "properties" => $this->mysqli->real_escape_string(ZiProto::encode($user->Properties->toArray())),
                "flags" => $this->mysqli->real_escape_string(ZiProto::encode($user->Flags)),
                "authentication_method" => $this->mysqli->real_escape_string($user->AuthenticationMethod),
                "authentication_properties" => $this->mysqli->real_escape_string(ZiProto::encode($user->AuthenticationProperties->toArray())),
                "private_access_token" => ($user->PrivateAccessToken !== null ? $this->mysqli->real_escape_string($user->PrivateAccessToken) : null),
                "coa_user_entity" => $this->mysqli->real_escape_string(ZiProto::encode($user->CoaUserEntity->toArray())),
                "profile" => $this->mysqli->real_escape_string(ZiProto::encode($user->Profile->toArray())),
                "settings" => $this->mysqli->real_escape_string(ZiProto::encode($user->Settings->toArray())),
                "privacy_state" => $this->mysqli->real_escape_string($user->PrivacyState),
                "last_activity_timestamp" => $this->mysqli->real_escape_string($user->LastActivityTimestamp),
            ], "id", $user->ID);

            $QueryResults = $this->mysqli->query($Query);

            if($QueryResults)
            {
                return $user;
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying to update the user",
                    $Query, $this->mysqli->error, $this->mysqli
                );
            }
        }

        /**
         * Checks if the given username exists on the network or not
         *
         * @param string $username
         * @return bool
         */
        public function checkUsernameExists(string $username): bool
        {
            try
            {
                $this->getUser(UserSearchMethod::ByUsername, $username);
                return true;
            }
            catch(Exception $e)
            {
                return false;
            }
        }
    }
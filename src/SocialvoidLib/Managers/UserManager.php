<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    /** @noinspection PhpUnused */


    namespace SocialvoidLib\Managers;

    use Exception;
    use GearmanTask;
    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod;
    use SocialvoidLib\Abstracts\StatusStates\UserPrivacyState;
    use SocialvoidLib\Abstracts\StatusStates\UserStatus;
    use SocialvoidLib\Abstracts\UserAuthenticationMethod;
    use SocialvoidLib\Classes\Converter;
    use SocialvoidLib\Classes\Standard\BaseIdentification;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\GenericInternal\BackgroundWorkerNotEnabledException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidFirstNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidLastNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidUsernameException;
    use SocialvoidLib\Exceptions\Standard\Validation\UsernameAlreadyExistsException;
    use SocialvoidLib\Objects\User;
    use SocialvoidLib\Service\Jobs\UserManager\GetUserJob;
    use SocialvoidLib\Service\Jobs\UserManager\GetUserJobResults;
    use SocialvoidLib\SocialvoidLib;
    use ZiProto\ZiProto;

    /**
     * Class UserManager
     * @package SocialvoidLib\Managers
     */
    class UserManager
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * UserManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
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
         * @throws PeerNotFoundException
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
                "public_id" => $this->socialvoidLib->getDatabase()->real_escape_string($public_id),
                "username" => $this->socialvoidLib->getDatabase()->real_escape_string($username),
                "username_safe" => $this->socialvoidLib->getDatabase()->real_escape_string(strtolower($username)),
                "network" => $this->socialvoidLib->getDatabase()->real_escape_string($this->socialvoidLib->getNetworkConfiguration()["Domain"]),
                "status" => $this->socialvoidLib->getDatabase()->real_escape_string(UserStatus::Active),
                "status_change_timestamp" => 0,
                "properties" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($UserProperties->toArray())),
                "flags" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "authentication_method" => $this->socialvoidLib->getDatabase()->real_escape_string(UserAuthenticationMethod::None),
                "authentication_properties" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($UserAuthenticationProperties->toArray())),
                "private_access_token" => null,
                "coa_user_entity" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($CoaUserEntity->toArray())),
                "profile" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($Profile->toArray())),
                "settings" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($Settings->toArray())),
                "privacy_state" => $this->socialvoidLib->getDatabase()->real_escape_string(UserPrivacyState::Public),
                "last_activity_timestamp" => (int)$timestamp,
                "created_timestamp" => (int)$timestamp
            ]);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                return $this->getUser(UserSearchMethod::ByPublicId, $public_id);
            }
            else
            {
                throw new DatabaseException("There was an error while trying to register the user",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
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
         * @throws PeerNotFoundException
         */
        public function getUser(string $search_method, $value): User
        {
            switch($search_method)
            {
                case UserSearchMethod::ById:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = (int)$value;
                    break;

                case UserSearchMethod::ByPublicId:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = $this->socialvoidLib->getDatabase()->real_escape_string($value);
                    break;

                case UserSearchMethod::ByUsername:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = $this->socialvoidLib->getDatabase()->real_escape_string(strtolower($value));
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

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new PeerNotFoundException();
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
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
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
                "username" => $this->socialvoidLib->getDatabase()->real_escape_string($user->Username),
                "username_safe" => $this->socialvoidLib->getDatabase()->real_escape_string($user->UsernameSafe),
                "network" => $this->socialvoidLib->getDatabase()->real_escape_string($user->Network),
                "status" => $user->Status,
                "status_change_timestamp" => ($user->StatusChangeTimestamp !== null ? (int)$user->StatusChangeTimestamp : null),
                "properties" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($user->Properties->toArray())),
                "flags" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($user->Flags)),
                "authentication_method" => $this->socialvoidLib->getDatabase()->real_escape_string($user->AuthenticationMethod),
                "authentication_properties" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($user->AuthenticationProperties->toArray())),
                "private_access_token" => ($user->PrivateAccessToken !== null ? $this->socialvoidLib->getDatabase()->real_escape_string($user->PrivateAccessToken) : null),
                "coa_user_entity" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($user->CoaUserEntity->toArray())),
                "profile" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($user->Profile->toArray())),
                "settings" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($user->Settings->toArray())),
                "privacy_state" => $this->socialvoidLib->getDatabase()->real_escape_string($user->PrivacyState),
                "last_activity_timestamp" => $this->socialvoidLib->getDatabase()->real_escape_string($user->LastActivityTimestamp),
            ], "id", $user->ID);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                return $user;
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying to update the user",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
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
            // TODO: Optimize this to request less data
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


        /**
         * Fetches a multiple user queries, this function performs faster with BackgroundWorker enabled
         *
         * @param GetUserJob[] $jobs
         * @return GetUserJobResults[]
         * @throws BackgroundWorkerNotEnabledException
         */
        public function getMultipleUsers(array $jobs): array
        {
            if(Utilities::getBoolDefinition("SOCIALVOID_LIB_BACKGROUND_WORKER_ENABLED"))
            {
                $results = [];
                $context_id = hash("crc32b", time());

                /** @noinspection PhpUnhandledExceptionInspection */
                $this->socialvoidLib->getBackgroundWorker()->getClient()->getGearmanClient()->clearCallbacks();

                /** @noinspection PhpUnhandledExceptionInspection */
                $this->socialvoidLib->getBackgroundWorker()->getClient()->getGearmanClient()->setCompleteCallback(
                    function(GearmanTask $task, $context) use (&$results, $context_id)
                    {
                        if($context == "user_lookup_" . $context_id)
                        {
                            if($task->data() == null)
                                return;

                            $results[] = GetUserJobResults::fromArray(ZiProto::decode($task->data()));
                        }
                    }
                );

                // If background worker is enabled, split the query into multiple workers to speed up the process
                foreach($jobs as $job)
                {
                    $this->socialvoidLib->getBackgroundWorker()->getClient()->getGearmanClient()->addTask(
                        "get_user", ZiProto::encode($job->toArray()), "user_lookup_" . $context_id
                    );
                }

                // Run the massive fetch!
                $this->socialvoidLib->getBackgroundWorker()->getClient()->getGearmanClient()->runTasks();

                return $results;
            }
            else
            {
                $results = [];

                foreach($jobs as $job)
                {
                    $JobResults = new GetUserJobResults();
                    $JobResults->JobID = $job->JobID;

                    try
                    {
                        $JobResults->User = $this->getUser($job->SearchMethod, $job->Value);
                    }
                    catch(Exception $e)
                    {
                        $JobResults->User = null;
                    }

                    $results[] = $JobResults;
                }

                return $results;
            }
        }
    }
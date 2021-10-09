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

    use BackgroundWorker\Exceptions\ServerNotReachableException;
    use Exception;
    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\ContentSource;
    use SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod;
    use SocialvoidLib\Abstracts\StatusStates\UserPrivacyState;
    use SocialvoidLib\Abstracts\StatusStates\UserStatus;
    use SocialvoidLib\Abstracts\Types\CacheEntryObjectType;
    use SocialvoidLib\Abstracts\Types\Security\DocumentAccessType;
    use SocialvoidLib\Abstracts\UserAuthenticationMethod;
    use SocialvoidLib\Classes\Converter;
    use SocialvoidLib\Classes\Standard\BaseIdentification;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\GenericInternal\BackgroundWorkerNotEnabledException;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\CacheMissedException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\DependencyError;
    use SocialvoidLib\Exceptions\GenericInternal\DisplayPictureException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\GenericInternal\RedisCacheException;
    use SocialvoidLib\Exceptions\GenericInternal\ServiceJobException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidFirstNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidLastNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidUsernameException;
    use SocialvoidLib\Exceptions\Standard\Validation\UsernameAlreadyExistsException;
    use SocialvoidLib\InputTypes\DocumentInput;
    use SocialvoidLib\InputTypes\RegisterCacheInput;
    use SocialvoidLib\Objects\Document;
    use SocialvoidLib\Objects\User;
    use SocialvoidLib\SocialvoidLib;
    use udp2\Abstracts\ColorScheme;
    use udp2\Abstracts\DefaultAvatarType;
    use Zimage\Zimage;
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
         * @throws CacheException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
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

            if($last_name !== null)
            {
                if(Validate::lastName($last_name) == false)
                    throw new InvalidLastNameException("The given last name is invalid or empty", $last_name);
            }

            if($this->checkUsernameExists($username))
                throw new UsernameAlreadyExistsException("The given username is already registered on the network", $username);

            $Profile = new User\Profile();
            $Profile->FirstName = Converter::emptyString($first_name);
            $Profile->LastName = Converter::emptyString($last_name);

            $UserProperties = new User\UserProperties();
            $UserAuthenticationProperties = new User\UserAuthenticationProperties();
            $Settings = new User\UserSettings();

            $timestamp = time();
            $public_id = BaseIdentification::userPublicId($timestamp);
            $slave_server = $this->socialvoidLib->getSlaveManager()->getRandomMySqlServer();

            $Query = QueryBuilder::insert_into("users", [
                "public_id" => $this->socialvoidLib->getDatabase()->real_escape_string($public_id),
                "username" => $this->socialvoidLib->getDatabase()->real_escape_string($username),
                "username_safe" => $this->socialvoidLib->getDatabase()->real_escape_string(strtolower($username)),
                "network" => $this->socialvoidLib->getDatabase()->real_escape_string($this->socialvoidLib->getMainConfiguration()["MainDomain"]),
                "status" => $this->socialvoidLib->getDatabase()->real_escape_string(UserStatus::Active),
                "status_change_timestamp" => 0,
                "properties" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($UserProperties->toArray())),
                "flags" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "authentication_method" => $this->socialvoidLib->getDatabase()->real_escape_string(UserAuthenticationMethod::None),
                "authentication_properties" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($UserAuthenticationProperties->toArray())),
                "private_access_token" => null,
                "profile" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($Profile->toArray())),
                "settings" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($Settings->toArray())),
                "privacy_state" => $this->socialvoidLib->getDatabase()->real_escape_string(UserPrivacyState::Public),
                "slave_server" => $this->socialvoidLib->getDatabase()->real_escape_string($slave_server->MysqlServerPointer->HashPointer),
                "last_activity_timestamp" => $timestamp,
                "created_timestamp" => $timestamp
            ]);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $ReturnResults = $this->getUser(UserSearchMethod::ByPublicId, $public_id);
                $this->registerUserCacheEntry($ReturnResults);

                return $ReturnResults;
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
         * @param string $search_method
         * @param string|int $value
         * @return User
         * @throws CacheException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
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

            if($this->socialvoidLib->getRedisBasicCacheConfiguration()["Enabled"])
            {
                $CachedUser = $this->getUserCacheEntry($value);
                if($CachedUser !== null) return $CachedUser;
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
                "profile",
                "settings",
                "privacy_state",
                "slave_server",
                "last_activity_timestamp",
                "created_timestamp"
            ], $search_method, $value, null, null, 1);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new PeerNotFoundException("The requested peer was not found in the network");
                }
                else
                {
                    $Row["properties"] = ZiProto::decode($Row["properties"]);
                    $Row["flags"] = ZiProto::decode($Row["flags"]);
                    $Row["authentication_properties"] = ZiProto::decode($Row["authentication_properties"]);
                    $Row["profile"] = ZiProto::decode($Row["profile"]);
                    $Row["settings"] = ZiProto::decode($Row["settings"]);

                    $ReturnResults = User::fromArray($Row);
                    $DisplayPictureDocument = $this->getDisplayPictureDocument($ReturnResults);
                    $ReturnResults->DisplayPictureDocument = $DisplayPictureDocument;
                    $this->registerUserCacheEntry($ReturnResults);

                    return $ReturnResults;
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
         * @throws CacheException
         */
        public function updateUser(User $user): User
        {
            $user->LastActivityTimestamp = time();
            $Query = QueryBuilder::update("users", [
                "username" => $this->socialvoidLib->getDatabase()->real_escape_string($user->Username),
                "username_safe" => $this->socialvoidLib->getDatabase()->real_escape_string($user->UsernameSafe),
                "network" => $this->socialvoidLib->getDatabase()->real_escape_string($user->Network),
                "status" => $user->Status,
                "status_change_timestamp" => ($user->StatusChangeTimestamp !== null ? $user->StatusChangeTimestamp : null),
                "properties" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($user->Properties->toArray())),
                "flags" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($user->Flags)),
                "authentication_method" => $this->socialvoidLib->getDatabase()->real_escape_string($user->AuthenticationMethod),
                "authentication_properties" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($user->AuthenticationProperties->toArray())),
                "private_access_token" => ($user->PrivateAccessToken !== null ? $this->socialvoidLib->getDatabase()->real_escape_string($user->PrivateAccessToken) : null),
                "profile" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($user->Profile->toArray())),
                "settings" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($user->Settings->toArray())),
                "privacy_state" => $this->socialvoidLib->getDatabase()->real_escape_string($user->PrivacyState),
                "last_activity_timestamp" => $this->socialvoidLib->getDatabase()->real_escape_string($user->LastActivityTimestamp),
            ], "id", $user->ID);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $this->registerUserCacheEntry($user);
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
         * @param array $query
         * @param bool $skip_errors
         * @param int $utilization
         * @return User[]
         * @throws BackgroundWorkerNotEnabledException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         * @throws InvalidSearchMethodException
         * @throws PeerNotFoundException
         * @throws ServerNotReachableException
         * @throws ServiceJobException
         */
        public function getMultipleUsers(array $query, bool $skip_errors=True, int $utilization=15): array
        {
            if(Utilities::getBoolDefinition("SOCIALVOID_LIB_BACKGROUND_WORKER_ENABLED"))
            {
                return $this->socialvoidLib->getServiceJobManager()->getUserJobs()->resolveUsers(
                    $query, $utilization, $skip_errors
                );
            }
            else
            {
                $return_results = [];

                foreach($query as $value => $search_method)
                {
                    try
                    {
                        $return_results[] = $this->getUser($search_method, $value);
                    }
                    catch(Exception $e)
                    {
                        if($skip_errors == false) throw $e;
                    }
                }

                return $return_results;
            }
        }

        /**
         * Registers a user cache entry
         *
         * @param User $user
         * @throws CacheException
         */
        private function registerUserCacheEntry(User $user): void
        {
            if($this->socialvoidLib->getRedisBasicCacheConfiguration()["Enabled"])
            {
                $CacheEntryInput = new RegisterCacheInput();
                $CacheEntryInput->ObjectType = CacheEntryObjectType::User;
                $CacheEntryInput->ObjectData = $user->toArray();
                $CacheEntryInput->Pointers = [$user->ID, $user->PublicID, $user->UsernameSafe];

                try
                {
                    $this->socialvoidLib->getBasicRedisCacheManager()->registerCache(
                        $CacheEntryInput,
                        $this->socialvoidLib->getRedisBasicCacheConfiguration()["PeerCacheTTL"],
                        $this->socialvoidLib->getRedisBasicCacheConfiguration()["PeerCacheLimit"]
                    );
                }
                catch(Exception $e)
                {
                    throw new CacheException("There was an error while trying to register the peer cache entry", 0, $e);
                }
            }
        }

        /**
         * Gets a user cache entry, returns null if it's a miss
         *
         * @param string $value
         * @return User|null
         * @throws CacheException
         */
        private function getUserCacheEntry(string $value): ?User
        {
            if($this->socialvoidLib->getRedisBasicCacheConfiguration()["Enabled"] == false)
                throw new CacheException("BasicRedisCache is not enabled");

            try
            {
                $CacheEntryResults = $this->socialvoidLib->getBasicRedisCacheManager()->getCacheEntry(
                    CacheEntryObjectType::User, $value);
            }
            catch (CacheMissedException $e)
            {
                return null;
            }
            catch (DependencyError | RedisCacheException $e)
            {
                throw new CacheException("There was an issue while trying to request a user cache entry", 0, $e);
            }

            return User::fromArray($CacheEntryResults->ObjectData);
        }

        /**
         * Gets the default profile picture of the user
         *
         * @param User $user
         * @return Document
         * @throws CacheException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         */
        public function getDefaultDisplayPictureDocument(User &$user): Document
        {
            if($user->Properties->DefaultProfilePictureDocumentID == null)
            {
                try
                {

                    $this->socialvoidLib->getUserDisplayPictureManager()->generateAvatar(
                        $user->PublicID . '_default', $user->getDisplayName(),
                        DefaultAvatarType::InitialsBase, ColorScheme::Dark
                    );

                    $avatar_zimage = $this->socialvoidLib->getUserDisplayPictureManager()->getAvatar($user->PublicID . "_default");
                }
                catch(Exception $e)
                {
                    throw new DisplayPictureException('There was an error while trying to process the default profile picture', 0, $e);
                }

                $document_input = new DocumentInput();
                $document_input->AccessType = DocumentAccessType::Public;
                $document_input->OwnerUserID = $user->ID;
                $document_input->ContentSource = ContentSource::UserProfilePicture;
                $document_input->ContentIdentifier = $user->PublicID . "_default";

                try
                {
                    $document_input->Files = Converter::zimageToFiles($avatar_zimage, $user->PublicID . "_default");
                }
                catch(Exception $e)
                {
                    throw new DisplayPictureException('There was an error while trying to convert a Zimage to a file', 0, $e);
                }

                $user->Properties->DefaultProfilePictureDocumentID = $this->socialvoidLib->getDocumentsManager()->createDocument($document_input);
                $user->DisplayPictureDocument = $this->socialvoidLib->getDocumentsManager()->getDocument($user->Properties->DefaultProfilePictureDocumentID);
                $this->updateUser($user);

                return $user->DisplayPictureDocument;
            }

            try
            {
                return $this->socialvoidLib->getDocumentsManager()->getDocument($user->Properties->DefaultProfilePictureDocumentID);
            }
            catch (DocumentNotFoundException $e)
            {
                $user->Properties->DefaultProfilePictureDocumentID = null;
                return $this->getDefaultDisplayPictureDocument($user);
            }
        }

        /**
         * Gets the profile picture of the user, if none is set then it will return the default value.
         *
         * @param User $user
         * @return Document
         * @throws CacheException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         */
        public function getDisplayPictureDocument(User &$user): Document
        {
            if($user->Properties->ProfilePictureDocumentID == null)
                return $this->getDefaultDisplayPictureDocument($user);

            try
            {
                return $this->socialvoidLib->getDocumentsManager()->getDocument($user->Properties->ProfilePictureDocumentID);
            }
            catch (DocumentNotFoundException $e)
            {
                return $this->getDefaultDisplayPictureDocument($user);
            }
        }

        /**
         * Applies a new profile picture to the user
         *
         * @param User $user
         * @param string $filePath
         * @throws CacheException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws DisplayPictureException
         */
        public function setDisplayPicture(User &$user, string $filePath)
        {
            try
            {
                $this->socialvoidLib->getUserDisplayPictureManager()->applyAvatar($filePath, $user->PublicID);
                $avatar_zimage = $this->socialvoidLib->getUserDisplayPictureManager()->getAvatar($user->PublicID);
            }
            catch(Exception $e)
            {
                throw new DisplayPictureException('There was an error while trying to apply the display picture', 0, $e);
            }

            $document_input = new DocumentInput();
            $document_input->AccessType = DocumentAccessType::Public;
            $document_input->OwnerUserID = $user->ID;
            $document_input->ContentSource = ContentSource::UserProfilePicture;
            $document_input->ContentIdentifier = $user->PublicID;

            try
            {
                $document_input->Files = Converter::zimageToFiles($avatar_zimage, $user->PublicID);
            }
            catch(Exception $e)
            {
                throw new DisplayPictureException('There was an error while trying convert a zimage to a file', 0, $e);
            }

            $new_document_id = $this->socialvoidLib->getDocumentsManager()->createDocument($document_input);

            if($user->Properties->ProfilePictureDocumentID !== null)
            {
                $old_document = $this->getDisplayPictureDocument($user);

                try
                {
                    $this->socialvoidLib->getDocumentsManager()->deleteDocument($old_document);
                }
                catch (DatabaseException $e)
                {
                    unset($e);
                }
            }

            $user->Properties->ProfilePictureDocumentID = $new_document_id;
            $user->DisplayPictureDocument = $this->socialvoidLib->getDocumentsManager()->getDocument($new_document_id);
            $this->updateUser($user);
        }

        /**
         * Deletes the users profile picture if it exists
         *
         * @param User $user
         * @throws CacheException
         * @throws DatabaseException
         * @throws DisplayPictureException
         * @throws DocumentNotFoundException
         \*/
        public function deleteDisplayPicture(User &$user)
        {
            if($user->Properties->ProfilePictureDocumentID !== null)
            {
                $old_document = $this->getDisplayPictureDocument($user);
                $this->socialvoidLib->getDocumentsManager()->deleteDocument($old_document);

                $user->Properties->ProfilePictureDocumentID = null;
                $user->DisplayPictureDocument = $this->getDefaultDisplayPictureDocument($user);
                $this->updateUser($user);
            }
        }

        /**
         * Returns the path of the user display picture
         *
         * @param User $user
         * @return Zimage
         * @throws DisplayPictureException
         */
        public function getDisplayPicture(User &$user): Zimage
        {
            try
            {
                return $this->socialvoidLib->getUserDisplayPictureManager()->getAvatar($this->getDisplayPictureDocument($user)->ContentIdentifier);
            }
            catch(Exception $e)
            {
                throw new DisplayPictureException('There was an error while trying get a existing Zimage file', 0, $e);
            }
        }
    }
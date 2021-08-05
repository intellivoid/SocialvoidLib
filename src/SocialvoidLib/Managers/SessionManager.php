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
    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\SearchMethods\ActiveSessionSearchMethod;
    use SocialvoidLib\Abstracts\Types\CacheEntryObjectType;
    use SocialvoidLib\Abstracts\UserAuthenticationMethod;
    use SocialvoidLib\Classes\Standard\BaseIdentification;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\CacheMissedException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\DependencyError;
    use SocialvoidLib\Exceptions\GenericInternal\DeprecatedComponentException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\GenericInternal\RedisCacheException;
    use SocialvoidLib\Exceptions\GenericInternal\SecurityException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionNotFoundException;
    use SocialvoidLib\InputTypes\RegisterCacheInput;
    use SocialvoidLib\InputTypes\SessionClient;
    use SocialvoidLib\Objects\ActiveSession;
    use SocialvoidLib\Objects\ActiveSession\SessionData;
    use SocialvoidLib\Objects\Standard\SessionEstablished;
    use SocialvoidLib\SocialvoidLib;
    use tsa\Classes\Crypto;
    use ZiProto\ZiProto;

    /**
     * Class SessionManager
     * @package SocialvoidLib\Managers
     */
    class SessionManager
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * SessionManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Creates a new session clients
         *
         * @param SessionClient $sessionClient
         * @param string $ip_address
         * @return SessionEstablished
         * @throws DatabaseException
         * @throws SecurityException
         */
        public function createSession(SessionClient $sessionClient, string $ip_address): SessionEstablished
        {
            $SessionData = new SessionData();
            $SessionSecurity = new ActiveSession\SessionSecurity();
            $SessionSecurity->ClientPublicHash = $sessionClient->PublicHash;
            $SessionSecurity->ClientPrivateHash = $sessionClient->PrivateHash;

            try
            {
                $SessionSecurity->HashChallenge = Crypto::BuildSecretSignature(64);
            }
            catch(Exception $e)
            {
                throw new SecurityException("There was an exception while trying to build the secret signature", 0, $e);
            }

            $Timestamp = time();
            $ExpiresTimestamp = $Timestamp + 600; // 10 Minutes due to no authentication
            $ID = BaseIdentification::sessionId($sessionClient, $SessionSecurity);

            /** @noinspection PhpBooleanCanBeSimplifiedInspection */
            $Query = QueryBuilder::insert_into("sessions", [
                "id" => $this->socialvoidLib->getDatabase()->real_escape_string($ID),
                "flags" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "authenticated" => $this->socialvoidLib->getDatabase()->real_escape_string((int)false),
                "user_id" => null,
                "authentication_method_used" => UserAuthenticationMethod::None,
                "platform" => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($sessionClient->Platform)),
                "client_name" => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($sessionClient->Name)),
                "client_version" => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($sessionClient->Version)),
                "ip_address" => $this->socialvoidLib->getDatabase()->real_escape_string($ip_address),
                "data" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($SessionData->toArray())),
                "security" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($SessionSecurity->toArray())),
                "last_active_timestamp" => $Timestamp,
                "created_timestamp" => $Timestamp,
                "expires_timestamp" => $ExpiresTimestamp
            ]);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);
            if($QueryResults)
            {
                $SessionEstablishedObject = new SessionEstablished();
                $SessionEstablishedObject->ID = $ID;
                $SessionEstablishedObject->Challenge = $SessionSecurity->HashChallenge;

                return $SessionEstablishedObject;
            }
            else
            {
                throw new DatabaseException("There was an error while trying to create a session",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Returns an existing session from the database
         *
         * @param string $search_method
         * @param string $value
         * @return ActiveSession
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws SessionNotFoundException
         * @throws CacheException
         * @noinspection PhpDocMissingThrowsInspection
         */
        public function getSession(string $search_method, string $value): ActiveSession
        {
            switch($search_method)
            {
                case ActiveSessionSearchMethod::ById:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = $this->socialvoidLib->getDatabase()->real_escape_string($value);
                    break;


                /** @noinspection PhpDeprecationInspection */
                case ActiveSessionSearchMethod::ByPublicId:
                    /** @noinspection PhpUnhandledExceptionInspection */
                    throw new DeprecatedComponentException("The search method  'ByPublicId' is deprecated, use 'ById' instead.");

                default:
                    throw new InvalidSearchMethodException("The given search method is not applicable to getSession()", $search_method, $value);
            }

            if($this->socialvoidLib->getRedisBasicCacheConfiguration()["Enabled"] && $this->socialvoidLib->getRedisBasicCacheConfiguration()["SessionCacheEnabled"])
            {
                $CachedPost = $this->getSessionCacheEntry($value);
                if($CachedPost !== null) return $CachedPost;
            }

            $Query = QueryBuilder::select("sessions", [
                "id",
                "flags",
                "authenticated",
                "user_id",
                "authentication_method_used",
                "platform",
                "client_name",
                "client_version",
                "ip_address",
                "data",
                "security",
                "last_active_timestamp",
                "created_timestamp",
                "expires_timestamp"
            ], $search_method, $value, null, null, 1);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new SessionNotFoundException();
                }
                else
                {
                    $Row["flags"] = ZiProto::decode($Row["flags"]);
                    $Row["data"] = ZiProto::decode($Row["data"]);
                    $Row["security"] = ZiProto::decode($Row["security"]);

                    $returnResults = ActiveSession::fromArray($Row);
                    $this->registerSessionCacheEntry($returnResults);
                    return $returnResults;
                }
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying retrieve the session from the network",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Updates an existing session in the database
         *
         * @param ActiveSession $activeSession
         * @return ActiveSession
         * @throws CacheException
         * @throws DatabaseException
         */
        public function updateSession(ActiveSession $activeSession): ActiveSession
        {
            $activeSession->LastActiveTimestamp = time();
            $activeSession->ExpiresTimestamp = time() + 600;

            if($activeSession->Authenticated && $activeSession->UserID !== null)
                $activeSession->ExpiresTimestamp = time() + 259200;

            $Query = QueryBuilder::update("sessions", [
                "flags" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($activeSession->Flags)),
                "authenticated" => (int)$activeSession->Authenticated,
                "user_id" => ($activeSession->UserID == null ? null : (int)$activeSession->UserID),
                "authentication_method_used" => ($activeSession->UserID == null ?
                    $this->socialvoidLib->getDatabase()->real_escape_string(UserAuthenticationMethod::None) :
                    $this->socialvoidLib->getDatabase()->real_escape_string($activeSession->AuthenticationMethodUsed)
                ),
                "ip_address" => $this->socialvoidLib->getDatabase()->real_escape_string($activeSession->IpAddress),
                "data" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($activeSession->Data->toArray())),
                "last_active_timestamp" => $activeSession->LastActiveTimestamp,
                "expires_timestamp" => $activeSession->ExpiresTimestamp
            ], "id", $activeSession->ID);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $this->registerSessionCacheEntry($activeSession);
                return $activeSession;
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying to update the session",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Registers a session cache entry
         *
         * @param ActiveSession $activeSession
         * @throws CacheException
         */
        private function registerSessionCacheEntry(ActiveSession $activeSession): void
        {
            if(
                $this->socialvoidLib->getRedisBasicCacheConfiguration()["Enabled"] &&
                $this->socialvoidLib->getRedisBasicCacheConfiguration()["SessionCacheEnabled"]
            )
            {
                $CacheEntryInput = new RegisterCacheInput();
                $CacheEntryInput->ObjectType = CacheEntryObjectType::Session;
                $CacheEntryInput->ObjectData = $activeSession->toArray();
                $CacheEntryInput->Pointers = [$activeSession->ID];

                try
                {
                    $this->socialvoidLib->getBasicRedisCacheManager()->registerCache(
                        $CacheEntryInput,
                        $this->socialvoidLib->getRedisBasicCacheConfiguration()["SessionCacheTTL"],
                        $this->socialvoidLib->getRedisBasicCacheConfiguration()["SessionCacheLimit"]
                    );
                }
                catch(Exception $e)
                {
                    throw new CacheException("There was an error while trying to register the session cache entry", 0, $e);
                }
            }
        }

        /**
         * Gets a session cache entry
         *
         * @param string $value
         * @return ActiveSession|null
         * @throws CacheException
         */
        private function getSessionCacheEntry(string $value): ?ActiveSession
        {
            if($this->socialvoidLib->getRedisBasicCacheConfiguration()["Enabled"] == false)
                throw new CacheException("BasicRedisCache is not enabled");

            if($this->socialvoidLib->getRedisBasicCacheConfiguration()["SessionCacheEnabled"] == false)
                return null;

            try
            {
                $CacheEntryResults = $this->socialvoidLib->getBasicRedisCacheManager()->getCacheEntry(
                    CacheEntryObjectType::Session, $value);
            }
            catch (CacheMissedException $e)
            {
                return null;
            }
            catch (DependencyError | RedisCacheException $e)
            {
                throw new CacheException("There was an issue while trying to request a session cache entry", 0, $e);
            }

            return ActiveSession::fromArray($CacheEntryResults->ObjectData);
        }

    }
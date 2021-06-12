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

    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\SearchMethods\ActiveSessionSearchMethod;
    use SocialvoidLib\Classes\Standard\BaseIdentification;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionNotFoundException;
    use SocialvoidLib\InputTypes\SessionClient;
    use SocialvoidLib\InputTypes\SessionDevice;
    use SocialvoidLib\Objects\ActiveSession;
    use SocialvoidLib\Objects\ActiveSession\SessionCache;
    use SocialvoidLib\Objects\ActiveSession\SessionData;
    use SocialvoidLib\Objects\User;
    use SocialvoidLib\SocialvoidLib;
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
         * @param SessionDevice $sessionDevice
         * @param User $user
         * @param string $authentication_method_used
         * @param string $ip_address
         * @return string
         * @throws DatabaseException
         */
        public function createSession(
            SessionClient $sessionClient, SessionDevice $sessionDevice,
            User $user, string $authentication_method_used, string $ip_address): string
        {
            $SessionCache = new SessionCache();
            $SessionData = new SessionData();
            $Timestamp = (int)time();
            $PublicID = BaseIdentification::sessionId($user->ID, $sessionClient, $sessionDevice);

            $Query = QueryBuilder::insert_into("sessions", [
                "public_id" => $this->socialvoidLib->getDatabase()->real_escape_string($PublicID),
                "flags" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "authenticated" => $this->socialvoidLib->getDatabase()->real_escape_string((int)true),
                "user_id" => (int)$user->ID,
                "authentication_method_used" => $authentication_method_used,
                "device_model" => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($sessionDevice->DeviceModel)),
                "platform" => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($sessionDevice->Platform)),
                "system_version" => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($sessionDevice->SystemVersion)),
                "client_name" => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($sessionClient->Name)),
                "client_version" => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($sessionClient->Version)),
                "ip_address" => $this->socialvoidLib->getDatabase()->real_escape_string($ip_address),
                "session_cache" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($SessionCache->toArray())),
                "session_data" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($SessionData->toArray())),
                "last_active_timestamp" => $Timestamp,
                "created_timestamp" => $Timestamp
            ]);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);
            if($QueryResults)
            {
                return $PublicID;
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
         */
        public function getSession(string $search_method, string $value): ActiveSession
        {
            switch($search_method)
            {
                case ActiveSessionSearchMethod::ById:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = (int)$value;
                    break;

                case ActiveSessionSearchMethod::ByPublicId:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = $this->socialvoidLib->getDatabase()->real_escape_string($value);
                    break;

                default:
                    throw new InvalidSearchMethodException("The given search method is not applicable to getSession()", $search_method, $value);
            }

            $Query = QueryBuilder::select("sessions", [
                "id",
                "public_id",
                "flags",
                "authenticated",
                "user_id",
                "authentication_method_used",
                "device_model",
                "platform",
                "system_version",
                "client_name",
                "client_version",
                "ip_address",
                "session_cache",
                "session_data",
                "last_active_timestamp",
                "created_timestamp"
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
                    $Row["session_cache"] = ZiProto::decode($Row["session_cache"]);
                    $Row["session_data"] = ZiProto::decode($Row["session_data"]);

                    return(ActiveSession::fromArray($Row));
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
         * @throws DatabaseException
         */
        public function updateSession(ActiveSession $activeSession): ActiveSession
        {
            $activeSession->LastActiveTimestamp = (int)time();
            $Query = QueryBuilder::update("sessions", [
                "flags" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($activeSession->Flags)),
                "authenticated" => (int)$activeSession->Authenticated,
                "ip_address" => $this->socialvoidLib->getDatabase()->real_escape_string($activeSession->IpAddress),
                "session_cache" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($activeSession->SessionCache->toArray())),
                "session_data" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($activeSession->SessionData->toArray())),
                "last_active_timestamp" => (int)$activeSession->LastActiveTimestamp
            ], "id", (int)$activeSession->ID);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
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
    }
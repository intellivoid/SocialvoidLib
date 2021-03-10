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

    use SocialvoidLib\Abstracts\Flags\ActiveSessionFlag;
    use SocialvoidLib\Abstracts\UserAuthenticationMethod;
    use SocialvoidLib\Objects\ActiveSession\SessionCache;
    use SocialvoidLib\Objects\ActiveSession\SessionData;

    /**
     * Class ActiveSession
     * @package SocialvoidLib\Objects
     */
    class ActiveSession
    {
        /**
         * The Unique Internal Database ID for this record
         *
         * @var int
         */
        public $ID;

        /**
         * The Unique Public ID for the current session
         *
         * @var string
         */
        public $PublicID;

        /**
         * The current flags set to this session
         *
         * @var ActiveSessionFlag[]
         */
        public $Flags;

        /**
         * Indicates if the current session is still authenticated
         *
         * @var bool
         */
        public $Authenticated;

        /**
         * The User ID that this session is for
         *
         * @var int
         */
        public $UserID;

        /**
         * The authentication method used to create this session
         *
         * @var UserAuthenticationMethod
         */
        public $AuthenticationMethodUsed;

        /**
         * The device used by the client
         *
         * @var string|null
         */
        public $DeviceModel;

        /**
         * The platform used by the client
         *
         * @var string|null
         */
        public $Platform;

        /**
         * The version of the system used
         *
         * @var string|null
         */
        public $SystemVersion;

        /**
         * The name of the client used
         *
         * @var string
         */
        public $ClientName;

        /**
         * The version specified by the client
         *
         * @var string
         */
        public $ClientVersion;

        /**
         * The last known IP address to use this session
         *
         * @var string
         */
        public $IpAddress;

        /**
         * The session cache data used by the server for optimized response times
         *
         * @var SessionCache
         */
        public $SessionCache;

        /**
         * @return int
         */
        public function getID(): int
        {
            return $this->ID;
        }

        /**
         * @param int $ID
         */
        public function setID(int $ID): void
        {
            $this->ID = $ID;
        }

        /**
         * @return string
         */
        public function getPublicID(): string
        {
            return $this->PublicID;
        }

        /**
         * @param string $PublicID
         */
        public function setPublicID(string $PublicID): void
        {
            $this->PublicID = $PublicID;
        }

        /**
         * @return ActiveSessionFlag[]
         */
        public function getFlags(): array
        {
            return $this->Flags;
        }

        /**
         * @param ActiveSessionFlag[] $Flags
         */
        public function setFlags(array $Flags): void
        {
            $this->Flags = $Flags;
        }

        /**
         * @return bool
         */
        public function isAuthenticated(): bool
        {
            return $this->Authenticated;
        }

        /**
         * @param bool $Authenticated
         */
        public function setAuthenticated(bool $Authenticated): void
        {
            $this->Authenticated = $Authenticated;
        }

        /**
         * @return int
         */
        public function getUserID(): int
        {
            return $this->UserID;
        }

        /**
         * @param int $UserID
         */
        public function setUserID(int $UserID): void
        {
            $this->UserID = $UserID;
        }

        /**
         * @return UserAuthenticationMethod
         */
        public function getAuthenticationMethodUsed(): UserAuthenticationMethod
        {
            return $this->AuthenticationMethodUsed;
        }

        /**
         * @param UserAuthenticationMethod $AuthenticationMethodUsed
         */
        public function setAuthenticationMethodUsed(UserAuthenticationMethod $AuthenticationMethodUsed): void
        {
            $this->AuthenticationMethodUsed = $AuthenticationMethodUsed;
        }

        /**
         * @return string|null
         */
        public function getDeviceModel(): ?string
        {
            return $this->DeviceModel;
        }

        /**
         * @param string|null $DeviceModel
         */
        public function setDeviceModel(?string $DeviceModel): void
        {
            $this->DeviceModel = $DeviceModel;
        }

        /**
         * @return string|null
         */
        public function getPlatform(): ?string
        {
            return $this->Platform;
        }

        /**
         * @param string|null $Platform
         */
        public function setPlatform(?string $Platform): void
        {
            $this->Platform = $Platform;
        }

        /**
         * @return string|null
         */
        public function getSystemVersion(): ?string
        {
            return $this->SystemVersion;
        }

        /**
         * @param string|null $SystemVersion
         */
        public function setSystemVersion(?string $SystemVersion): void
        {
            $this->SystemVersion = $SystemVersion;
        }

        /**
         * @return string
         */
        public function getClientName(): string
        {
            return $this->ClientName;
        }

        /**
         * @param string $ClientName
         */
        public function setClientName(string $ClientName): void
        {
            $this->ClientName = $ClientName;
        }

        /**
         * @return string
         */
        public function getClientVersion(): string
        {
            return $this->ClientVersion;
        }

        /**
         * @param string $ClientVersion
         */
        public function setClientVersion(string $ClientVersion): void
        {
            $this->ClientVersion = $ClientVersion;
        }

        /**
         * @return string
         */
        public function getIpAddress(): string
        {
            return $this->IpAddress;
        }

        /**
         * @param string $IpAddress
         */
        public function setIpAddress(string $IpAddress): void
        {
            $this->IpAddress = $IpAddress;
        }

        /**
         * @return SessionCache
         */
        public function getSessionCache(): SessionCache
        {
            return $this->SessionCache;
        }

        /**
         * @param SessionCache $SessionCache
         */
        public function setSessionCache(SessionCache $SessionCache): void
        {
            $this->SessionCache = $SessionCache;
        }

        /**
         * @return SessionData
         */
        public function getSessionData(): SessionData
        {
            return $this->SessionData;
        }

        /**
         * @param SessionData $SessionData
         */
        public function setSessionData(SessionData $SessionData): void
        {
            $this->SessionData = $SessionData;
        }

        /**
         * @return int
         */
        public function getLastActiveTimestamp(): int
        {
            return $this->LastActiveTimestamp;
        }

        /**
         * @param int $LastActiveTimestamp
         */
        public function setLastActiveTimestamp(int $LastActiveTimestamp): void
        {
            $this->LastActiveTimestamp = $LastActiveTimestamp;
        }

        /**
         * @return int
         */
        public function getCreatedTimestamp(): int
        {
            return $this->CreatedTimestamp;
        }

        /**
         * @param int $CreatedTimestamp
         */
        public function setCreatedTimestamp(int $CreatedTimestamp): void
        {
            $this->CreatedTimestamp = $CreatedTimestamp;
        }

        /**
         * The data associated with this session
         *
         * @var SessionData
         */
        public $SessionData;

        /**
         * The Unix Timestamp for when this session was last active
         *
         * @var int
         */
        public $LastActiveTimestamp;

        /**
         * The Unix Timestamp for when this session was created
         *
         * @var int
         */
        public $CreatedTimestamp;

        /**
         * ActiveSession constructor.
         */
        public function __construct()
        {
            $this->SessionCache = new SessionCache();
            $this->SessionData = new SessionData();
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "id" => $this->ID,
                "public_id" => $this->PublicID,
                "flags" => $this->Flags,
                "authenticated" => $this->Authenticated,
                "user_id" => $this->UserID,
                "authentication_method_used" => $this->AuthenticationMethodUsed,
                "device_model" => $this->DeviceModel,
                "platform" => $this->Platform,
                "system_version" => $this->SystemVersion,
                "client_name" => $this->ClientName,
                "client_version" => $this->ClientVersion,
                "ip_address" => $this->IpAddress,
                "session_cache" => $this->SessionCache->toArray(),
                "session_data" => $this->SessionData->toArray(),
                "last_active_timestamp" => $this->LastActiveTimestamp,
                "created_timestamp" => $this->CreatedTimestamp
            ];
        }

        /**
         * Constructs the object from an array
         *
         * @param array $data
         * @return ActiveSession
         */
        public static function fromArray(array $data): ActiveSession
        {
            $ActiveSessionObject = new ActiveSession();

            if(isset($data["id"]))
            {
                if($data["id"] !== null)
                    $ActiveSessionObject->ID = (int)$data["id"];
            }

            if(isset($data["public_id"]))
                $ActiveSessionObject->PublicID = $data["public_id"];

            if(isset($data["flags"]))
                $ActiveSessionObject->Flags = $data["flags"];

            if(isset($data["authenticated"]))
            {
                if($data["authenticated"] !== null)
                    $ActiveSessionObject->Authenticated = (bool)$data["authenticated"];
            }

            if(isset($data["user_id"]))
            {
                if($data["user_id"] !== null)
                    $ActiveSessionObject->UserID = (int)$data["user_id"];
            }

            if(isset($data["authentication_method_used"]))
                $ActiveSessionObject->AuthenticationMethodUsed = $data["authentication_method_used"];

            if(isset($data["device_model"]))
                $ActiveSessionObject->DeviceModel = $data["device_model"];

            if(isset($data["platform"]))
                $ActiveSessionObject->Platform = $data["platform"];

            if(isset($data["system_version"]))
                $ActiveSessionObject->SystemVersion = $data["system_version"];

            if(isset($data["client_name"]))
                $ActiveSessionObject->ClientName = $data["client_name"];

            if(isset($data["client_version"]))
                $ActiveSessionObject->ClientVersion =  $data["client_version"];

            if(isset($data["ip_address"]))
                $ActiveSessionObject->IpAddress = $data["ip_address"];

            if(isset($data["session_cache"]))
                $ActiveSessionObject->SessionCache = SessionCache::fromArray($data["session_cache"]);

            if(isset($data["session_data"]))
                $ActiveSessionObject->SessionData = SessionData::fromArray($data["session_data"]);

            if(isset($data["last_active_timestamp"]))
            {
                if($data["last_active_timestamp"] !== null)
                {
                    $ActiveSessionObject->LastActiveTimestamp = (int)$data["last_active_timestamp"];

                    if(((int)time() - $ActiveSessionObject->LastActiveTimestamp) >= 1209600) // Two weeks
                    {
                        $ActiveSessionObject->Authenticated = false;
                    }
                }
            }

            if(isset($data["created_timestamp"]))
            {
                if($data["created_timestamp"] !== null)
                    $ActiveSessionObject->CreatedTimestamp = (int)$data["created_timestamp"];
            }

            return $ActiveSessionObject;
        }
    }
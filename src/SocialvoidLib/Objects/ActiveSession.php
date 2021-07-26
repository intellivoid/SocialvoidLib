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
    use SocialvoidLib\Objects\ActiveSession\SessionData;
    use SocialvoidLib\Objects\ActiveSession\SessionSecurity;

    /**
     * Class ActiveSession
     * @package SocialvoidLib\Objects
     */
    class ActiveSession
    {
        /**
         * The Unique ID for this record
         *
         * @var string
         */
        public $ID;

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
         * The platform used by the client
         *
         * @var string|null
         */
        public $Platform;

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
         * @return SessionData
         */
        public function getSessionData(): SessionData
        {
            return $this->Data;
        }

        /**
         * @param SessionData $SessionData
         */
        public function setSessionData(SessionData $SessionData): void
        {
            $this->Data = $SessionData;
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
         * @return SessionSecurity
         */
        public function getSecurity(): SessionSecurity
        {
            return $this->Security;
        }

        /**
         * @param SessionSecurity $Security
         */
        public function setSecurity(SessionSecurity $Security): void
        {
            $this->Security = $Security;
        }

        /**
         * @return int
         */
        public function getExpiresTimestamp(): int
        {
            return $this->ExpiresTimestamp;
        }

        /**
         * @param int $ExpiresTimestamp
         */
        public function setExpiresTimestamp(int $ExpiresTimestamp): void
        {
            $this->ExpiresTimestamp = $ExpiresTimestamp;
        }

        /**
         * The data associated with this session
         *
         * @var SessionData
         */
        public $Data;

        /**
         * The security data associated with this session
         *
         * @var SessionSecurity
         */
        public $Security;

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
         * The Unix Timestamp for when this session expires
         *
         * @var int
         */
        public $ExpiresTimestamp;

        /**
         * ActiveSession constructor.
         * @noinspection PhpPureAttributeCanBeAddedInspection
         */
        public function __construct()
        {
            $this->Data = new SessionData();
            $this->Security = new SessionSecurity();
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         * @noinspection PhpPureAttributeCanBeAddedInspection
         */
        public function toArray(): array
        {
            return [
                "id" => $this->ID,
                "flags" => $this->Flags,
                "authenticated" => $this->Authenticated,
                "user_id" => $this->UserID,
                "authentication_method_used" => $this->AuthenticationMethodUsed,
                "platform" => $this->Platform,
                "client_name" => $this->ClientName,
                "client_version" => $this->ClientVersion,
                "ip_address" => $this->IpAddress,
                "data" => $this->Data->toArray(),
                "security" => $this->Security->toArray(),
                "last_active_timestamp" => $this->LastActiveTimestamp,
                "created_timestamp" => $this->CreatedTimestamp,
                "expires_timestamp" => $this->ExpiresTimestamp
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
                    $ActiveSessionObject->ID = $data["id"];
            }

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

            if(isset($data["platform"]))
                $ActiveSessionObject->Platform = $data["platform"];

            if(isset($data["client_name"]))
                $ActiveSessionObject->ClientName = $data["client_name"];

            if(isset($data["client_version"]))
                $ActiveSessionObject->ClientVersion =  $data["client_version"];

            if(isset($data["ip_address"]))
                $ActiveSessionObject->IpAddress = $data["ip_address"];

            if(isset($data["data"]))
                $ActiveSessionObject->Data = SessionData::fromArray($data["data"]);

            if(isset($data["security"]))
                $ActiveSessionObject->Security = SessionSecurity::fromArray($data["security"]);

            if(isset($data["last_active_timestamp"]))
            {
                if($data["last_active_timestamp"] !== null)
                {
                    $ActiveSessionObject->LastActiveTimestamp = (int)$data["last_active_timestamp"];

                    if((time() - $ActiveSessionObject->LastActiveTimestamp) >= 1209600) // Two weeks
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

            if(isset($data["expires_timestamp"]))
            {
                if($data["expires_timestamp"] !== null)
                    $ActiveSessionObject->ExpiresTimestamp = (int)$data["expires_timestamp"];
            }

            return $ActiveSessionObject;
        }
    }
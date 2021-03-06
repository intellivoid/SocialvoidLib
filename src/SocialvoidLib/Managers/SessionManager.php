<?php


    namespace SocialvoidLib\Managers;

    use msqg\QueryBuilder;
    use SocialvoidLib\Classes\Standard\BaseIdentification;
    use SocialvoidLib\InputTypes\SessionClient;
    use SocialvoidLib\InputTypes\SessionDevice;
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

        public function createSession(
            SessionClient $sessionClient, SessionDevice $sessionDevice,
            User $user, string $authentication_method_used, string $ip_address)
        {
            $SessionCache = new SessionCache();
            $SessionData = new SessionData();
            $Timestamp = (int)time();
            $PublicID = BaseIdentification::SessionID($user->ID, $sessionClient, $sessionDevice, $Timestamp);

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
        }
    }
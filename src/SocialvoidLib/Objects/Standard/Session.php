<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Objects\ActiveSession;

    /**
     * Class Session
     * @package SocialvoidLib\Objects\Standard
     */
    class Session
    {
        /**
         * The Session ID
         *
         * @var string
         */
        public $ID;

        /**
         * Array of flags associated with this session
         *
         * @var array
         */
        public $Flags;

        /**
         * Indicates if the session is authenticated or not
         *
         * @var bool
         */
        public $Authenticated;

        /**
         * The Unix Timestamp for when this session was first created
         *
         * @var int
         */
        public $EstablishedTimestamp;

        /**
         * The Unix Timestamp for when this session expires
         *
         * @var int
         */
        public $ExpiresTimestamp;

        /**
         * Returns an array representation of the object
         *
         * @return array
         * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
         */
        public function toArray(): array
        {
            return [
                "id" => $this->ID,
                "flags" => $this->Flags,
                "authenticated" => $this->Authenticated,
                "created" => $this->EstablishedTimestamp,
                "expires" => $this->ExpiresTimestamp
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return Session
         * @noinspection PhpPureAttributeCanBeAddedInspection
         */
        public static function fromArray(array $data): Session
        {
            $SessionObject = new Session();

            if(isset($data["id"]))
                $SessionObject->ID = $data["id"];

            if(isset($data["flags"]))
                $SessionObject->Flags = $data["flags"];

            if(isset($data["authenticated"]))
                $SessionObject->Authenticated = $data["authenticated"];

            if(isset($data["created"]))
                $SessionObject->EstablishedTimestamp = $data["created"];

            if(isset($data["expires"]))
                $SessionObject->ExpiresTimestamp = $data["expires"];

            return $SessionObject;
        }

        /**
         * Constructs object from an ActiveSession object
         *
         * @param ActiveSession $activeSession
         * @return Session
         * @noinspection PhpPureAttributeCanBeAddedInspection
         * @noinspection PhpUnused
         */
        public static function fromActiveSession(ActiveSession $activeSession): Session
        {
            $SessionObject = new Session();

            $SessionObject->ID = $activeSession->ID;
            $SessionObject->Authenticated = ($activeSession->Authenticated && $activeSession->UserID !== null);
            $SessionObject->EstablishedTimestamp = $activeSession->CreatedTimestamp;
            $SessionObject->ExpiresTimestamp = $activeSession->ExpiresTimestamp;
            $SessionObject->Flags = $activeSession->Flags;

            return $SessionObject;
        }
    }
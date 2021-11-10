<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Types\BuiltinTypes;
    use SocialvoidLib\Interfaces\StandardObjectInterface;
    use SocialvoidLib\Objects\ActiveSession;

    /**
     * Class Session
     * @package SocialvoidLib\Objects\Standard
     */
    class Session implements StandardObjectInterface
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
         * An array of permissions assigned to the current session
         * 
         * @var array
         */
        public $Permissions;

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
         */
        public function toArray(): array
        {
            return [
                'id' => $this->ID,
                'flags' => $this->Flags,
                'permissions' => $this->Permissions,
                'authenticated' => $this->Authenticated,
                'created' => $this->EstablishedTimestamp,
                'expires' => $this->ExpiresTimestamp
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return Session
         */
        public static function fromArray(array $data): Session
        {
            $SessionObject = new Session();

            if(isset($data['id']))
                $SessionObject->ID = $data['id'];

            if(isset($data['flags']))
                $SessionObject->Flags = $data['flags'];

            if(isset($data['permissions']))
                $SessionObject->Permissions = $data['permissions'];

            if(isset($data['authenticated']))
                $SessionObject->Authenticated = $data['authenticated'];

            if(isset($data['created']))
                $SessionObject->EstablishedTimestamp = $data['created'];

            if(isset($data['expires']))
                $SessionObject->ExpiresTimestamp = $data['expires'];

            return $SessionObject;
        }

        /**
         * Constructs object from an ActiveSession object
         *
         * @param ActiveSession $activeSession
         * @return Session
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
            $SessionObject->Permissions = $activeSession->Data->PermissionSets;

            return $SessionObject;
        }

        /**
         * @inheritDoc
         */
        public static function getName(): string
        {
            return 'Session';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'A session object is contains basic information about the session.';
        }

        /**
         * @inheritDoc
         */
        public static function getDefinition(): ObjectDefinition
        {
            return new ObjectDefinition(self::getName(), self::getDescription(), self::getParameters());
        }

        /**
         * @inheritDoc
         */
        public static function getParameters(): array
        {
            return [
                new ParameterDefinition('id', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The ID of the session obtained when establishing a session'),

                new ParameterDefinition('flags', [
                    new TypeDefinition(BuiltinTypes::String, true)
                ], true, 'An array of flags that has been set to this session'),

                new ParameterDefinition('permissions', [
                    new TypeDefinition(BuiltinTypes::String, true)
                ], true, 'An array of permission sets that has been set to this session'),

                new ParameterDefinition('authenticated', [
                    new TypeDefinition(BuiltinTypes::Boolean, false)
                ], true, 'Indicates if the session is currently authenticated to a user'),

                new ParameterDefinition('created', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The Unix Timestamp for when this session was first created'),

                new ParameterDefinition('expires', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The Unix Timestamp for when this session expires'),
            ];
        }
    }
<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Types\BuiltinTypes;
    use SocialvoidLib\Interfaces\StandardObjectInterface;
    use SocialvoidLib\Objects\ActiveSession;

    /**
     * Class SessionEstablished
     * @package SocialvoidLib\Objects\Standard
     */
    class SessionEstablished implements StandardObjectInterface
    {
        /**
         * The Unique Session ID
         *
         * @var string
         */
        public $ID;

        /**
         * The Challenge that the client must complete regularly
         *
         * @var string
         */
        public $Challenge;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "id" => $this->ID,
                "challenge" => $this->Challenge
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return SessionEstablished
         */
        public static function fromArray(array $data): SessionEstablished
        {
            $sessionEstablishedObject = new SessionEstablished();

            if(isset($data["id"]))
                $sessionEstablishedObject->ID = $data["id"];

            if(isset($data["challenge"]))
                $sessionEstablishedObject->Challenge = $data["challenge"];

            return $sessionEstablishedObject;
        }

        /**
         * Constructs object from another object reference
         *
         * @param ActiveSession $activeSession
         * @return SessionEstablished
         */
        public static function fromActiveSession(ActiveSession $activeSession): SessionEstablished
        {
            $sessionEstablishedObject = new SessionEstablished();

            $sessionEstablishedObject->Challenge = $activeSession->Security->HashChallenge;
            $sessionEstablishedObject->ID = $activeSession->ID;

            return $sessionEstablishedObject;
        }

        /**
         * @inheritDoc
         */
        public static function getName(): string
        {
            return 'SessionEstablished';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'A SessionEstablished object is returned when you create a session. This object returns basic information about the session that the server has created for you.';
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

                new ParameterDefinition('challenge', [
                    new TypeDefinition(BuiltinTypes::String, true)
                ], true, 'The TOTP based challenge secret')
            ];
        }
    }
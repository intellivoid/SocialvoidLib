<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Objects\ActiveSession;

    /**
     * Class SessionEstablished
     * @package SocialvoidLib\Objects\Standard
     */
    class SessionEstablished
    {
        /**
         * The Unique Session ID
         *
         * @var string
         */
        public $ID;

        /**
         * The Challenge Hash that the client must complete regularly
         *
         * @var string
         */
        public $ChallengeHash;

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
                "challenge_hash" => $this->ChallengeHash
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

            if(isset($data["challenge_hash"]))
                $sessionEstablishedObject->ChallengeHash = $data["challenge_hash"];

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

            $sessionEstablishedObject->ChallengeHash = $activeSession->Security->HashChallenge;
            $sessionEstablishedObject->ID = $activeSession->ID;

            return $sessionEstablishedObject;
        }
    }
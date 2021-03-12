<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Objects;

    use SocialvoidLib\Abstracts\StatusStates\CoaAuthenticationStatus;

    /**
     * Class CoaAuthentication
     * @package SocialvoidLib\Objects
     */
    class CoaAuthentication
    {
        /**
         * The Unique Internal Database ID for this record
         *
         * @var int
         */
        public $ID;

        /**
         * The COA Account ID that this record is associated with
         *
         * @var string|null
         */
        public $AccountID;

        /**
         * The user ID that this coa authentication record owns
         *
         * @var int|null
         */
        public $UserID;

        /**
         * @var string|CoaAuthenticationStatus
         */
        public $Status;

        /**
         * The Unix Timestamp for when this record was last updated
         *
         * @var int
         */
        public $LastUpdatedTimestamp;

        /**
         * The Unix Timestamp for when this record was created
         *
         * @var int
         */
        public $CreatedTimestamp;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "id" => $this->ID,
                "account_id" => $this->AccountID,
                "user_id" => $this->UserID,
                "status" => $this->Status,
                "last_updated_timestamp" => $this->LastUpdatedTimestamp,
                "created_timestamp" => $this->CreatedTimestamp
            ];
        }

        /**
         * Constructs the object from an array representation
         * 
         * @param array $data
         * @return CoaAuthentication
         * @noinspection DuplicatedCode
         */
        public static function fromArray(array $data): CoaAuthentication
        {
            $CoaAuthenticationObject = new CoaAuthentication();

            if(isset($data["id"]))
                $CoaAuthenticationObject->ID = ($data["id"] == null ? null : (int)$data["id"]);

            if(isset($data["account_id"]))
                $CoaAuthenticationObject->AccountID = ($data["account_id"] == null ? null : $data["account_id"]);

            if(isset($data["user_id"]))
                $CoaAuthenticationObject->UserID = ($data["user_id"] == null ? null : (int)$data["user_id"]);

            if(isset($data["status"]))
                $CoaAuthenticationObject->Status = ($data["status"] == null ? null : $data["status"]);

            if(isset($data["last_updated_timestamp"]))
                $CoaAuthenticationObject->LastUpdatedTimestamp = ($data["last_updated_timestamp"] == null ? null : (int)$data["last_updated_timestamp"]);

            if(isset($data["created_timestamp"]))
                $CoaAuthenticationObject->CreatedTimestamp = ($data["created_timestamp"] == null ? null : (int)$data["created_timestamp"]);

            return $CoaAuthenticationObject;
        }
    }
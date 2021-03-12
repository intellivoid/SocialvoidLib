<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Managers;

    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\SearchMethods\CoaAuthenticationSearchMethod;
    use SocialvoidLib\Abstracts\StatusStates\CoaAuthenticationStatus;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Internal\CoaAuthenticationRecordNotFoundException;
    use SocialvoidLib\Objects\CoaAuthentication;
    use SocialvoidLib\SocialvoidLib;

    /**
     * Class CoaAuthenticationManager
     * @package SocialvoidLib\Managers
     */
    class CoaAuthenticationManager
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * CoaAuthenticationManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Registers a new record into the database
         *
         * @param string $account_id
         * @throws DatabaseException
         */
        public function registerRecord(string $account_id): void
        {
            $Query = QueryBuilder::insert_into("coa_authentication", [
                "account_id" => $this->socialvoidLib->getDatabase()->real_escape_string($account_id),
                "status" => $this->socialvoidLib->getDatabase()->real_escape_string(CoaAuthenticationStatus::Available),
                "last_updated_timestamp" => (int)time(),
                "created_timestamp" => (int)time()
            ]);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);
            if($QueryResults == false)
            {
                throw new DatabaseException("There was an error while trying to register the coa authentication record into thed database",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Returns an existing record from the database
         *
         * @param string $search_method
         * @param string $value
         * @return CoaAuthentication
         * @throws CoaAuthenticationRecordNotFoundException
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         */
        public function getRecord(string $search_method, string $value): CoaAuthentication
        {
            switch($search_method)
            {
                case CoaAuthenticationSearchMethod::ByUserId:
                case CoaAuthenticationSearchMethod::ById:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = (int)$value;
                    break;

                case CoaAuthenticationSearchMethod::ByAccountId:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = $this->socialvoidLib->getDatabase()->real_escape_string($value);
                    break;

                default:
                    throw new InvalidSearchMethodException("The search method is not applicable to this method", $search_method, $value);
            }

            $Query = QueryBuilder::select("coa_authentication", [
                "id",
                "account_id",
                "user_id",
                "status",
                "last_updated_timestamp",
                "created_timestamp"
            ], $search_method, $value, null, null, 1);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);
            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new CoaAuthenticationRecordNotFoundException();
                }

                return(CoaAuthentication::fromArray($Row));
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying retrieve the coa authentication record from the database",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Updates an existing COA Authentication Record in the database
         *
         * @param CoaAuthentication $coaAuthentication
         * @return CoaAuthentication
         * @throws DatabaseException
         */
        public function updateRecord(CoaAuthentication $coaAuthentication): CoaAuthentication
        {
            $coaAuthentication->LastUpdatedTimestamp = (int)time();
            $Query = QueryBuilder::update("coa_authentication", [
                "account_id" => ($coaAuthentication->AccountID == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string($coaAuthentication->AccountID)),
                "user_id" => ($coaAuthentication->UserID == null ? null : (int)$coaAuthentication->UserID),
                "status" => $this->socialvoidLib->getDatabase()->real_escape_string($coaAuthentication->Status),
                "last_updated_timestamp" => $coaAuthentication->LastUpdatedTimestamp
            ]);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);
            if($QueryResults == false)
            {
                throw new DatabaseException(
                    "There was an error while trying to update the coa authentication record",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }

            return $coaAuthentication;
        }

    }
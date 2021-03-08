<?php

    /** @noinspection PhpUnused */


    namespace SocialvoidLib\Managers;

    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\SearchMethods\FollowerDataSearchMethod;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Internal\FollowerDataNotFound;
    use SocialvoidLib\Objects\FollowerData;
    use SocialvoidLib\SocialvoidLib;
    use ZiProto\ZiProto;

    /**
     * Class FollowerDataManager
     * @package SocialvoidLib\Managers
     */
    class FollowerDataManager
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * FollowerDataManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Creates a new FollowerData record
         *
         * @param int $user_id
         * @throws DatabaseException
         */
        public function createRecord(int $user_id): void
        {
            $Query = QueryBuilder::insert_into("follower_data", [
                "user_id" => (int)$user_id,
                "followers" => 0,
                "followers_ids" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "following" => 0,
                "following_ids" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "last_updated_timestamp" => (int)time(),
                "created_timestamp" => (int)time()
            ]);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults == false)
            {
                throw new DatabaseException("There was an error while trying to register the following data",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Gets an existing record from the database
         *
         * @param string $search_method
         * @param string $value
         * @return FollowerData
         * @throws DatabaseException
         * @throws FollowerDataNotFound
         * @throws InvalidSearchMethodException
         */
        public function getRecord(string $search_method, string $value): FollowerData
        {
            switch($search_method)
            {
                case FollowerDataSearchMethod::ByUserId:
                case FollowerDataSearchMethod::ById:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = (int)$value;
                    break;

                default:
                    throw new InvalidSearchMethodException("The search method is not applicable to getRecord() in FollowerDataManager", $search_method, $value);
            }

            $Query = QueryBuilder::select("follower_data", [
                "id",
                "user_id",
                "followers",
                "followers_ids",
                "following",
                "followings_ids",
                "last_updated_timestamp",
                "created_timestamp"
            ], $search_method, $value, null, null, 1);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new FollowerDataNotFound();
                }
                else
                {
                    return(FollowerData::fromArray($Row));
                }
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying retrieve the following data record",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Updates an existing FollowerData record
         *
         * @param FollowerData $followerData
         * @return FollowerData
         * @throws DatabaseException
         */
        public function updateRecord(FollowerData $followerData): FollowerData
        {
            $followerData->LastUpdatedTimestamp = (int)time();
            $Query = QueryBuilder::update("follower_data", [
                "followers" => (int)$followerData->Followers,
                "followers_ids" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($followerData->FollowersIDs)),
                "following" => (int)$followerData->Following,
                "following_ids" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($followerData->FollowingIDs)),
                "last_updated_timestamp" => $followerData->LastUpdatedTimestamp
            ], "id", (int)$followerData->ID);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                return $followerData;
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying to update the following data record",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }
    }
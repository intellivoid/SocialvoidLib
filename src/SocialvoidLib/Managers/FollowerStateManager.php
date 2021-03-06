<?php

    /** @noinspection PhpUnused */

    namespace SocialvoidLib\Managers;

    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\SearchMethods\FollowerStateSearchMethod;
    use SocialvoidLib\Abstracts\StatusStates\FollowerState;
    use SocialvoidLib\Abstracts\StatusStates\UserPrivacyState;
    use SocialvoidLib\Classes\Standard\BaseIdentification;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Standard\Network\UserNotFoundException;
    use SocialvoidLib\Objects\Follower;
    use SocialvoidLib\Objects\User;
    use SocialvoidLib\SocialvoidLib;
    use ZiProto\ZiProto;

    /**
     * Class FollowerStateManager
     * @package SocialvoidLib\Managers
     */
    class FollowerStateManager
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * FollowerStateManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Registers a following state into the database
         *
         * @param User $user
         * @param User $target_user
         * @return FollowerState|string
         * @throws DatabaseException
         */
        public function registerFollowingState(User $user, User $target_user): string
        {
            $FollowerState = FollowerState::Following;

            if($target_user->PrivacyState == UserPrivacyState::Private)
            {
                $FollowerState = FollowerState::AwaitingApproval;
            }

            $PublicID = BaseIdentification::FollowingStateID($user->ID, $target_user->ID);

            $Query = QueryBuilder::insert_into("follower_states", [
                "public_id" => $this->socialvoidLib->getDatabase()->real_escape_string($PublicID),
                "user_id" => (int)$user->ID,
                "target_user_id" => (int)$target_user->ID,
                "state" => $this->socialvoidLib->getDatabase()->real_escape_string($FollowerState),
                "flags" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "last_updated_timestamp" => (int)time(),
                "created_timestamp" => (int)time()
            ]);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                return $FollowerState;
            }
            else
            {
                throw new DatabaseException("There was an error while trying to register the following state",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Gets an existing following state from the database
         *
         * @param string $search_method
         * @param string $value
         * @return Follower
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws UserNotFoundException
         */
        public function getFollowingState(string $search_method, string $value): Follower
        {
            switch($search_method)
            {
                case FollowerStateSearchMethod::ByPublicId:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = $this->socialvoidLib->getDatabase()->real_escape_string($value);
                    break;

                case FollowerStateSearchMethod::ById:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = (int)$value;
                    break;

                default:
                    throw new InvalidSearchMethodException("The given search method is not applicable to getFollowingState()", $search_method, $value);
            }

            $Query = QueryBuilder::select("follower_states", [
                "id",
                "public_id",
                "user_id",
                "target_user_id",
                "state",
                "flags",
                "last_updated_timestamp",
                "created_timestamp"
            ], $search_method, $value, null, null, 1);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new UserNotFoundException();
                }
                else
                {
                    $Row["flags"] = ZiProto::decode($Row["flags"]);

                    return(Follower::fromArray($Row));
                }
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying retrieve the following state",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Updates an existing following state
         *
         * @param Follower $follower
         * @return Follower
         * @throws DatabaseException
         */
        public function updateFollowingState(Follower $follower): Follower
        {
            $Query = QueryBuilder::update("follower_states", [
                "state" => $this->socialvoidLib->getDatabase()->real_escape_string($follower->State),
                "flags" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($follower->Flags)),
                "last_updated_timestamp" => (int)time()
            ], "id", (int)$follower->ID);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                return $follower;
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying to update the following state",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }
    }
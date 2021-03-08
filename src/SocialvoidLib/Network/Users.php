<?php


    namespace SocialvoidLib\Network;

    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\User;

    /**
     * Class Users
     * @package SocialvoidLib\Network
     */
    class Users
    {
        /**
         * @var NetworkSession
         */
        private NetworkSession $networkSession;

        /**
         * Users constructor.
         * @param NetworkSession $networkSession
         */
        public function __construct(NetworkSession $networkSession)
        {
            $this->networkSession = $networkSession;
        }

        public function follow(User $peer): bool
        {
             $this->networkSession->getSocialvoidLib()->getFollowerStateManager()->getFollowingState("")
        }
    }
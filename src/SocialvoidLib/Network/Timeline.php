<?php


    namespace SocialvoidLib\Network;


    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Post;

    /**
     * Class Timeline
     * @package SocialvoidLib\Network
     */
    class Timeline
    {
        /**
         * @var NetworkSession
         */
        private NetworkSession $networkSession;

        /**
         * Timeline constructor.
         * @param NetworkSession $networkSession
         */
        public function __construct(NetworkSession $networkSession)
        {
            $this->networkSession = $networkSession;
        }

        public function postToTimeline(string $text, array $media_content=[], $flags=[]): Post
        {
        }
    }
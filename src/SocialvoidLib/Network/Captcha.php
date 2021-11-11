<?php

    namespace SocialvoidLib\Network;

    use SocialvoidLib\NetworkSession;

    class Captcha
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

    }
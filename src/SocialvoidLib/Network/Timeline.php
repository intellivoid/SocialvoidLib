<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

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
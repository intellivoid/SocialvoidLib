<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    /** @noinspection PhpUnused */

    namespace SocialvoidLib\Abstracts;

    /**
     * Class UserAuthenticationMethod
     * @package SocialvoidLib\Abstracts
     */
    abstract class UserAuthenticationMethod
    {
        /**
         * There is no authentication method available to this user,
         * this prevents authentication at all and might be used for bots
         * or strictly with an access token
         *
         * (For interpretation in the future, this leaves space for more ideas)
         */
        const None = "NONE";

        /**
         * The user uses a simple password to authenticate to the network
         */
        const Simple = "SIMPLE";

        /**
         * The user uses a simple password to authenticate to the network and
         * a one-time login code generated by a time-based authentication system
         * like Google Authenticator
         */
        const SimpleSecured = "SIMPLE_SECURED";

        /**
         * The user uses a CrossOverAuthentication provider to authenticate to the network
         */
        const CrossOverAuthentication = "COA";

        /**
         * The user uses a private access token to authenticate to the network, this option is
         * not meant to be set to a users account but rather displayed by the network
         */
        const PrivateAccessToken = "PRIVATE_ACCESS_TOKEN";
    }
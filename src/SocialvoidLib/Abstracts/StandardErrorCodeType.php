<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Abstracts;

    /**
     * Class StandardErrorCodeType
     * @package SocialvoidLib\Abstracts
     */
    abstract class StandardErrorCodeType
    {
        /**
         * An unknown error code range, non-standard
         */
        const Unknown = "UNKNOWN_ERROR";

        /**
         * 21-Set Error codes (Validation)
         * 8448 - 8703
         *
         * A error was raised in relation to the data given by the client being invalid
         */
        const ValidationError = "VALIDATION_ERROR";

        /**
         * 22-Set Error codes (Authentication)
         * 8704 - 8960
         *
         * An error was raised in relation to the authentication made by the client
         */
        const AuthenticationError = "AUTHENTICATION_ERROR";

        /**
         * 23-Set Error codes (Media)
         * 8960 - 12543
         *
         * An error was raised in relation to the media content being uploaded or downloaded
         */
        const MediaError = "MEDIA_ERROR";

        /**
         * 31-Set error codes (Network)
         * 12544 - 16384
         *
         * AN error was made in relation to the network's response to the client's requests
         */
        const NetworkError = "NETWORK_ERROR";

        /**
         * 40-Set error codes (server)
         * 16384 - 24575
         *
         * All error codes made in relation to the network's server
         */
        const ServerError = "SERVER_ERROR";

        /**
         * 60-Set error codes (security)
         *
         * 24576 - *
         */
        const SecurityError = "SECURITY_ERROR";
    }
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
         * 8704 - 12543
         *
         * An error was raised in relation to the authentication made by the client
         */
        const AuthenticationError = "AUTHENTICATION_ERROR";

        /**
         * 31-Set error codes (Network)
         * 12544 - *
         *
         * AN error was made in relation to the network's response to the client's requests
         */
        const NetworkError = "NETWORK_ERROR";
    }
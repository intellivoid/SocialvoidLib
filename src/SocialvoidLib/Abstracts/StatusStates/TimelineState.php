<?php

    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Abstracts\StatusStates;

    /**
     * Class TimelineState
     * @package SocialvoidLib\Abstracts\StatusStates
     */
    abstract class TimelineState
    {
        /**
         * Indicates the timeline is currently available and running OK
         */
        const Available = "AVAILABLE";

        /**
         * Indicates the timeline is currently being updated
         */
        const Updating = "UPDATING";

        /**
         * Indicates the timeline is frozen and should not be updated
         */
        const Frozen = "FROZEN";
    }
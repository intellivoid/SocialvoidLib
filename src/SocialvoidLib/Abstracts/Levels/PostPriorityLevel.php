<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

namespace SocialvoidLib\Abstracts\Levels;

    /**
     * Class PostPriorityLevel
     * @package SocialvoidLib\Abstracts\Levels
     */
    abstract class PostPriorityLevel
    {
        /**
         * Will not be shown on the timeline
         */
        const None = "NONE";

        /**
         * Will be shown on the last items of the timeline
         */
        const Low = "LOW";

        /**
         * Will be shown on the middle of the timeline
         */
        const Medium = "MEDIUM";

        /**
         * Will be shown on the first items on the timeline
         */
        const High = "HIGH";

        /**
         * Will be first thing to see on the timeline
         */
        const ExtremelyHigh = "EXTREMELY_HIGH";
    }
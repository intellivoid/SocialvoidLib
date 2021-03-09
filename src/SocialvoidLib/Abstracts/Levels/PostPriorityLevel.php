<?php


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
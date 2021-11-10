<?php

    namespace SocialvoidLib\Interfaces;

    use SocialvoidLib\Objects\Standard\ObjectDefinition;
    use SocialvoidLib\Objects\Standard\ParameterDefinition;

    interface StandardObjectInterface
    {
        /**
         * Returns the name of the standard error
         *
         * @return string
         */
        public static function getName(): string;

        /**
         * Returns a description of the standard error
         *
         * @return string
         */
        public static function getDescription(): string;

        /**
         * Returns the type
         *
         * @return ParameterDefinition[]
         */
        public static function getParameters(): array;

        /**
         * Returns an object definition object of the standard object
         *
         * @return ObjectDefinition
         */
        public static function getDefinition(): ObjectDefinition;
    }
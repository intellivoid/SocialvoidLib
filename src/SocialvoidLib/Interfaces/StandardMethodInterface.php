<?php

    namespace SocialvoidLib\Interfaces;

    use SocialvoidLib\Abstracts\Flags\PermissionSets;
    use SocialvoidLib\Objects\Standard\MethodDefinition;
    use SocialvoidLib\Objects\Standard\ObjectDefinition;
    use SocialvoidLib\Objects\Standard\ParameterDefinition;

    interface StandardMethodInterface
    {
        /**
         * Returns the name of the method
         *
         * @return string
         */
        public static function getName(): string;

        /**
         * Returns the namespace that the method is under
         *
         * @return string
         */
        public static function getNamespace(): string;

        /**
         * Returns name of the method without the leading namespace
         *
         * @return string
         */
        public static function getMethodName(): string;

        /**
         * Returns the full name of the method with the leading namespace
         *
         * @return string
         */
        public static function getMethod(): string;

        /**
         * Returns a description of the method
         *
         * @return string
         */
        public static function getDescription(): string;

        /**
         * Returns all the parameters that this method accepts
         *
         * @return ParameterDefinition[]
         */
        public static function getParameters(): array;

        /**
         * Returns an array of permission requirements
         *
         * @return array|PermissionSets[]|string[]
         */
        public static function getPermissionRequirements(): array;

        /**
         * Returns a method definition object of the standard object
         *
         * @return MethodDefinition
         */
        public static function getDefinition(): MethodDefinition;
    }
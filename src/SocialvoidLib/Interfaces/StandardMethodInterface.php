<?php

    namespace SocialvoidLib\Interfaces;

    use SocialvoidLib\Abstracts\Flags\PermissionSets;
    use SocialvoidLib\Objects\Standard\MethodDefinition;
    use SocialvoidLib\Objects\Standard\ParameterDefinition;
    use SocialvoidLib\Objects\Standard\TypeDefinition;

    interface StandardMethodInterface
    {
        /**
         * Returns the namespace that the method is under
         *
         * @return string
         */
        public static function getStandardNamespace(): string;

        /**
         * Returns name of the method without the leading namespace
         *
         * @return string
         */
        public static function getStandardMethodName(): string;

        /**
         * Returns the full name of the method with the leading namespace
         *
         * @return string
         */
        public static function getStandardMethod(): string;

        /**
         * Returns a description of the method
         *
         * @return string
         */
        public static function getStandardDescription(): string;

        /**
         * Returns all the parameters that this method accepts
         *
         * @return ParameterDefinition[]
         */
        public static function getStandardParameters(): array;

        /**
         * Returns an array of permission requirements
         *
         * @return array|PermissionSets[]|string[]
         */
        public static function getStandardPermissionRequirements(): array;

        /**
         * Returns an array of possible error codes that this method may return
         *
         * @return int[]
         */
        public static function getStandardPossibleErrorCodes(): array;

        /**
         * Returns an array of types that this method can potentially return
         *
         * @return array|TypeDefinition
         */
        public static function getReturnTypes(): array;

        /**
         * Returns a method definition object of the standard object
         *
         * @return MethodDefinition
         */
        public static function getDefinition(): MethodDefinition;
    }
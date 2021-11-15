<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Types\BuiltinTypes;
    use SocialvoidLib\Interfaces\StandardObjectInterface;

    class ProtocolDefinitions implements StandardObjectInterface
    {
        /**
         * The version of the protocol
         *
         * @var string
         */
        public $Version;

        /**
         * An array of error definitions that the server uses
         *
         * @var ErrorDefinition[]
         */
        public $ErrorDefinitions;

        /**
         * An array of object definitions that the server uses
         *
         * @var ObjectDefinition[]
         */
        public $ObjectDefinitions;

        /**
         * Returns an array representation of the protocol definition object
         *
         * @return array[]
         */
        public function toArray(): array
        {
            $error_definitions = [];

            foreach($this->ErrorDefinitions as $definition)
                $error_definitions[] = $definition->toArray();

            $object_definitions = [];
            foreach($this->ObjectDefinitions as $definition)
                $object_definitions[] = $definition->toArray();

            return [
                'version' => $this->Version,
                'errors' => $error_definitions,
                'objects' => $object_definitions
            ];
        }

        /**
         * @inheritDoc
         */
        public static function getName(): string
        {
            return 'ProtocolDefinitions';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The ProtocolDefinitions object contains object definitions of what the server\'s version of the protocol has defined and what their use cases are. Much like a documentation representation in a object structure that can be understood by clients which allows for constructors during runtime.';
        }

        /**
         * @inheritDoc
         */
        public static function getDefinition(): ObjectDefinition
        {
            return new ObjectDefinition(self::getName(), self::getDescription(), self::getParameters());
        }

        /**
         * @inheritDoc
         */
        public static function getParameters(): array
        {
            return [
                new ParameterDefinition('version', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The version of the protocol being used by the server, eg; 1.0'),

                    new ParameterDefinition('errors', [
                    new TypeDefinition(ErrorDefinition::getName(), true)
                ], true, 'A list of error definitions defined by the server and protocol with their respective error codes and descriptions'),

                new ParameterDefinition('objects', [
                    new TypeDefinition(ObjectDefinition::getName(), true)
                ], true, 'A list of object definitions defined by the server and protocol with their respective descriptions, names and parameters.')
            ];
        }
    }
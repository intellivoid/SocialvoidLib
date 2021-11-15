<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Flags\PermissionSets;
    use SocialvoidLib\Abstracts\Types\BuiltinTypes;
    use SocialvoidLib\Interfaces\StandardObjectInterface;

    class MethodDefinition implements StandardObjectInterface
    {
        /**
         * The version of the protocol being used
         *
         * @var string
         */
        public $ProtocolVersion;

        /**
         * The namespace of the method
         *
         * @var string
         */
        public $Namespace;

        /**
         * The name of the method without the namespace
         *
         * @var string
         */
        public $MethodName;

        /**
         * The full name of the method and namespace
         *
         * @var string
         */
        public $Method;

        /**
         * The description of the method
         *
         * @var string
         */
        public $Description;

        /**
         * Prerequisite of permission sets that the session must have in order
         * to execute the method.
         *
         * @var PermissionSets[]|string[]
         */
        public $PermissionRequirements;

        /**
         * The parameters that the method accepts
         *
         * @var ParameterDefinition[]|array
         */
        public $Parameters;

        /**
         * An array of possible error codes
         *
         * @var int[]
         */
        public $PossibleErrorCodes;

        /**
         * @var TypeDefinition[]|array
         */
        public $ReturnTypes;

        /**
         * @param string|null $namespace
         * @param string|null $method_name
         * @param string|null $method
         * @param string|null $description
         * @param array|null $permission_requirements
         * @param array|null $parameters
         * @param array|null $possible_error_codes
         * @param array|null $return_types
         */
        public function __construct(
            ?string $namespace=null, ?string $method_name=null, ?string $method=null, ?string $description=null,
            ?array $permission_requirements=[], ?array $parameters=[], ?array $possible_error_codes=[], ?array $return_types=[]
        )
        {
            $this->ProtocolVersion = '1.0';
            $this->Namespace = $namespace;
            $this->MethodName = $method_name;
            $this->Method = $method;
            $this->Description = $description;
            $this->PermissionRequirements = $permission_requirements;
            $this->Parameters = $parameters;
            $this->PossibleErrorCodes = $possible_error_codes;
            $this->ReturnTypes = $return_types;
        }

        /**
         * @return string
         */
        public function getId(): string
        {
            return hash('crc32',  $this->ProtocolVersion . ':' . $this->Method);
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            $parameters = [];
            $return_types = [];

            foreach($this->Parameters as $parameter)
                $parameters[] = $parameter->toArray();

            foreach($this->ReturnTypes as $returnType)
                $return_types[] = $returnType->toArray();

            return [
                'id' => $this->getId(),
                'namespace' => $this->Namespace,
                'method_name' => $this->MethodName,
                'method' => $this->MethodName,
                'description' => $this->Description,
                'permission_requirements' => $this->PermissionRequirements,
                'parameters' => $parameters,
                'possible_error_codes' => $this->PossibleErrorCodes,
                'return_types' => $return_types
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return MethodDefinition
         */
        public static function fromArray(array $data): MethodDefinition
        {
            $method_definition = new MethodDefinition();

            if(isset($data['method_name']))
                $method_definition->MethodName = $data['method_name'];

            if(isset($data['namespace']))
                $method_definition->Namespace = $data['namespace'];

            if(isset($data['method']))
                $method_definition->Method = $data['method'];

            if(isset($data['description']))
                $method_definition->Description = $data['description'];

            if(isset($data['permission_requirements']))
                $method_definition->PermissionRequirements = $data['permission_requirements'];

            if(isset($data['parameters']))
            {
                foreach($data['parameters'] as $parameter)
                    $method_definition->Parameters[] = ParameterDefinition::fromArray($parameter);
            }

            if(isset($data['possible_error_codes']))
                $method_definition->PossibleErrorCodes = $data['possible_error_codes'];

            if(isset($data['return_types']))
            {
                foreach($data['return_types'] as $return_type)
                    $method_definition->ReturnTypes[] = TypeDefinition::fromArray($return_type);
            }

            return $method_definition;
        }

        /**
         * @inheritDoc
         */
        public static function getName(): string
        {
            return 'MethodDefinition';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The object MethodDefinition contains information about method, namespace, permission requirements and the parameters it accepts';
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
                new ParameterDefinition('id', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'A crc32 hash of the methods\'s ID following the value; <ProtocolVersion>:<MethodName> eg; 1.0:timelime.compose'),

                new ParameterDefinition('namespace', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The namespace of the method e.g., timeline, network, etc.'),

                new ParameterDefinition('method_name', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The name of the method without the namespace compose, like, repost, etc.'),

                new ParameterDefinition('method', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The full name of the method with the leading namespace e.g. timeline.compose, timeline.like'),

                new ParameterDefinition('description', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The description of the method'),

                new ParameterDefinition('permission_requirements', [
                    new TypeDefinition(BuiltinTypes::String, true)
                ], true, 'The array of permission requirements for this method'),

                new ParameterDefinition('return_types', [
                    new TypeDefinition(TypeDefinition::getName(), true)
                ], true, 'An array of possible return types'),

            ];
        }
    }
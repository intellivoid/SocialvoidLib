<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Flags\PermissionSets;

    class MethodDefinition
    {
        /**
         * The version of the protocol being used
         *
         * @var string
         */
        public $ProtocolVersion;

        /**
         * The user-friendly name of the method
         *
         * @var string
         */
        public $Name;

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

        public function __construct()
        {
            $this->ProtocolVersion = '1.0';
            $this->PermissionRequirements = [];
            $this->Parameters = [];
        }

        /**
         * @return string
         */
        public function getId(): string
        {
            return hash('crc32',  $this->ProtocolVersion . ':' . $this->Name);
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            $parameters = [];

            foreach($this->Parameters as $parameter)
                $parameters[] = $parameter->toArray();

            return [
                'protocol_version' => $this->ProtocolVersion,
                'name' => $this->Name,
                'method_name' => $this->MethodName,
                'namespace' => $this->Namespace,
                'method' => $this->MethodName,
                'description' => $this->Description,
                'permission_requirements' => $this->PermissionRequirements,
                'parameters' => $parameters
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

            if(isset($data['protocol_version']))
                $method_definition->ProtocolVersion = $data['protocol_version'];

            if(isset($data['name']))
                $method_definition->Name = $data['name'];

            if(isset($data['description']))
                $method_definition->Description = $data['description'];

            if(isset($data['permission_requirements']))
                $method_definition->PermissionRequirements = $data['permission_requirements'];

            if(isset($data['parameters']))
            {
                foreach($data['parameters'] as $parameter)
                    $method_definition->Parameters[] = ParameterDefinition::fromArray($parameter);
            }

            return $method_definition;
        }
    }
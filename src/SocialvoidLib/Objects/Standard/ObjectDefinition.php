<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Types\BuiltinTypes;
    use SocialvoidLib\Interfaces\StandardObjectInterface;

    class ObjectDefinition implements StandardObjectInterface
    {
        /**
         * The version of the protocol being used
         *
         * @var string
         */
        public $ProtocolVersion;

        /**
         * The name of the type
         *
         * @var string
         */
        public $Name;

        /**
         * The description of the type
         *
         * @var string
         */
        public $Description;

        /**
         * The structure of the object
         *
         * @var ParameterDefinition[]|array
         */
        public $Parameters;

        /**
         * @param string|null $name
         * @param string|null $description
         * @param array|null $parameters
         */
        public function __construct(?string $name=null, ?string $description=null, ?array $parameters=[])
        {
            $this->ProtocolVersion = '1.0';
            $this->Name = $name;
            $this->Description = $description;
            $this->Parameters = $parameters;
        }

        /**
         * @return string
         */
        public function getId(): string
        {
            return hash('crc32',  $this->ProtocolVersion . ':' . $this->Name);
        }

        /**
         * @return array
         */
        public function toArray(): array
        {
            $parameters = [];
            foreach($this->Parameters as $item)
                $parameters[] = $item->toArray();

            return [
                'id' => $this->getId(),
                'name' => $this->Name,
                'description' => $this->Description,
                'parameters' => $parameters
            ];
        }

        /**
         * Returns an array representation of the error definition
         *
         * @param array $data
         * @return ObjectDefinition
         */
        public static function fromArray(array $data): ObjectDefinition
        {
            $definition = new ObjectDefinition();

            if(isset($data['name']))
                $definition->Name = $data['name'];

            if(isset($data['description']))
                $definition->Description = $data['description'];

            if(isset($data['parameters']))
            {
                foreach($data['parameters'] as $parameters)
                    $definition->Parameters[] = ParameterDefinition::fromArray($parameters);
            }

            return $definition;
        }

        /**
         * @inheritDoc
         */
        public static function getName(): string
        {
            return 'ObjectDefinition';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The object ObjectDefinition explains the structure of a object that the server could return or work with.';
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
                ], true, 'A crc32 hash of the object\'s ID following the value; <ProtocolVersion>:<ObjectName> eg; 1.0:Peer'),

                new ParameterDefinition('name', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The name of the object'),

                new ParameterDefinition('description', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'A description of the object'),

                new ParameterDefinition('parameters', [
                    new TypeDefinition(ParameterDefinition::getName(), true)
                ], true, 'An array of ParameterDefinitions explaining the object structure'),

            ];
        }
    }
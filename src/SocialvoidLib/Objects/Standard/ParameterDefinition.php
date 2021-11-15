<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;


    use SocialvoidLib\Abstracts\Types\BuiltinTypes;
    use SocialvoidLib\Interfaces\StandardObjectInterface;

    class ParameterDefinition implements StandardObjectInterface
    {
        /**
         * The name of the parameter
         *
         * @var string
         */
        public $Name;

        /**
         * The types applicable to the parameter
         *
         * @var TypeDefinition[]|array
         */
        public $Types;

        /**
         * Indicates if the parameter is required or not
         *
         * @var bool
         */
        public $Required;

        /**
         * A description of the parameter
         *
         * @var string
         */
        public $Description;

        /**
         * @param string|null $name
         * @param array|null $types
         * @param bool|null $required
         * @param string|null $description
         */
        public function __construct(?string $name=null, ?array $types=[], ?bool $required=true, ?string $description=null)
        {
            $this->Name = $name;
            $this->Types = $types;
            $this->Required = $required;
            $this->Description = $description;
        }

        /**
         * @return array
         */
        public function toArray(): array
        {
            $types = [];

            foreach($this->Types as $type)
                $types[] = $type->toArray();

            return [
                'name' => $this->Name,
                'types' => $types,
                'required' => $this->Required,
                'description' => $this->Description
            ];
        }

        /**
         * Returns an array representation of the error definition
         *
         * @param array $data
         * @return ParameterDefinition
         */
        public static function fromArray(array $data): ParameterDefinition
        {
            $definition = new ParameterDefinition();

            if(isset($data['name']))
                $definition->Name = $data['name'];

            if(isset($data['types']))
            {
                foreacH($data['types'] as $type)
                    $definition->Types[] = TypeDefinition::fromArray($type);
            }

            if(isset($data['required']))
                $definition->Required = $data['required'];

            if(isset($data['description']))
                $definition->Description = $data['description'];

            return $definition;
        }

        /**
         * @inheritDoc
         */
        public static function getName(): string
        {
            return 'ParameterDefinition';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The object ParameterDefinition contains information about the parameters used and or available, usually represented within an array; this object indicates the availabe types, name, description and requirement of the parameter. This can be applied to object property structures or method parameters';
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
                new ParameterDefinition('name', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The name of the parameter'),

                new ParameterDefinition('types', [
                    new TypeDefinition(TypeDefinition::getName(), true)
                ], true, 'An array of types that are used for this parameter'),

                new ParameterDefinition('required', [
                    new TypeDefinition(BuiltinTypes::Boolean, false)
                ], true, 'Indicates if this parameter is required or not, for objects this will always be true.'),

                new ParameterDefinition('description', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The description of the parameter'),

            ];
        }
    }
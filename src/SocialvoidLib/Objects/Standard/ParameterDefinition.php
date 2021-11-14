<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;


    class ParameterDefinition
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
    }
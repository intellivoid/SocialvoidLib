<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Types\BuiltinTypes;
    use SocialvoidLib\Interfaces\StandardObjectInterface;

    class TypeDefinition implements StandardObjectInterface
    {
        /**
         * The name of the type, can be object type as well.
         *
         * @var string
         */
        public $Type;

        /**
         * Indicates if the type definition is to be represented as a vector (array/list)
         *
         * @var bool
         */
        public $Vector;

        /**
         * @param string|null $type
         * @param bool $vector
         */
        public function __construct(?string $type=null, bool $vector=false)
        {
            $this->Type = $type;
            $this->Vector = $vector;
        }

        /**
         * @return array
         */
        public function toArray(): array
        {
            return [
                'type' => $this->Type,
                'vector' => $this->Vector,
            ];
        }

        /**
         * Returns an array representation of the error definition
         *
         * @param array $data
         * @return TypeDefinition
         */
        public static function fromArray(array $data): TypeDefinition
        {
            $definition = new TypeDefinition();

            if(isset($data['type']))
                $definition->Type = $data['type'];

            if(isset($data['vector']))
                $definition->Vector = $data['vector'];

            return $definition;
        }

        /**
         * @inheritDoc
         */
        public static function getName(): string
        {
            return 'TypeDefinition';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The object TypeDefinition contains information about the defined type, the vector property can indicate if the type is being represented is a vector (list/array) and should be iterated';
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
                new ParameterDefinition('type', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The type of the value, can either be a builtin type or one of the pre-defined object being represented as a string, eg; string, Peer, null'),

                new ParameterDefinition('vector', [
                    new TypeDefinition(BuiltinTypes::Boolean, false)
                ], true, 'An array of types that are used for this parameterIndicates if the type is represented as a vector or not (List/Array)')
            ];
        }
    }
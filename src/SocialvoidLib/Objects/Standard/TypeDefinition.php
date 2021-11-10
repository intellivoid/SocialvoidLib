<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    class TypeDefinition
    {
        /**
         * @var string
         */
        public $Name;

        /**
         * The name of the type, can be object type as well.
         *
         * @var string
         */
        public $Type;

        /**
         * @param string|null $type
         * @param bool $vector
         */
        public function __construct(?string $type=null, bool $vector=false)
        {
            $this->Vector = $vector;
            $this->Type = $type;
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
    }
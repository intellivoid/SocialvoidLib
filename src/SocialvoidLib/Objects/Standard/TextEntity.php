<?php

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Types\BuiltinTypes;
    use SocialvoidLib\Abstracts\Types\Standard\TextEntityType;
    use SocialvoidLib\Interfaces\StandardObjectInterface;

    class TextEntity implements StandardObjectInterface
    {
        /**
         * The text entity type
         *
         * @var string|TextEntityType
         */
        public $Type;

        /**
         * The offset value which indicates where the text begins
         *
         * @var int
         */
        public $Offset;

        /**
         * The length of the value which indicates the length starting from the offset
         *
         * @var int
         */
        public $Length;

        /**
         * The value of the entity, for example if the text entity is a mention the value would be the
         * username without the @ prefix, if the text entity is a URL then the value will be the URL.
         *
         * The value will be null if the value isn't applicable
         *
         * @var string|null
         */
        public $Value;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'type' => $this->Type,
                'offset' => $this->Offset,
                'length' => $this->Length,
                'value' => $this->Value
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return TextEntity
         */
        public static function fromArray(array $data): TextEntity
        {
            $textEntityObject = new TextEntity();

            if(isset($data['type']))
                $textEntityObject->Type = $data['type'];

            if(isset($data['offset']))
                $textEntityObject->Offset = (int)$data['offset'];

            if(isset($data['length']))
                $textEntityObject->Length = (int)$data['length'];

            if(isset($data['value']))
                $textEntityObject->Value = $data['value'];

            return $textEntityObject;
        }

        /**
         * @inheritDoc
         */
        public static function getName(): string
        {
            return 'TextEntity';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The text entity object describes the text type, this is useful for clients to render the given text correctly. For example a "@mention" will have a TextEntity with the value mention. So that the client can perform an action when this entity is clicked.';
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
                ], true, 'The text entity type'),

                new ParameterDefinition('offset', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The offset for when the entity begins in the text'),

                new ParameterDefinition('length', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The length of the entity'),

                new ParameterDefinition('value', [
                    new TypeDefinition(BuiltinTypes::String, false),
                    new TypeDefinition(BuiltinTypes::Null, false)
                ], true, 'The value of the entity, for styling entities such as BOLD, ITALIC, etc. this value will be null, but for values such as MENTION, HASHTAG & URL the value will contain the respective value for the entity, for example a URL entity will contain a value of a http URL')
            ];
        }
    }
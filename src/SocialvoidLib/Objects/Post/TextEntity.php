<?php

    /** @noinspection DuplicatedCode */

    namespace SocialvoidLib\Objects\Post;

    use SocialvoidLib\Abstracts\Types\Standard\TextEntityType;

    class TextEntity
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
    }
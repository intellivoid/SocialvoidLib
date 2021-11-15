<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Types\BuiltinTypes;
    use SocialvoidLib\Interfaces\StandardObjectInterface;
    use SocialvoidLib\Objects\Standard\ObjectDefinition;
    use SocialvoidLib\Objects\Standard\ParameterDefinition;
    use SocialvoidLib\Objects\Standard\TypeDefinition;

    class DisplayPictureSize implements StandardObjectInterface
    {
        /**
         * The width of the image
         *
         * @var int
         */
        public $Width;

        /**
         * The height of the image
         *
         * @var int
         */
        public $Height;

        /**
         * The document
         *
         * @var Document
         */
        public $Document;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'width' => $this->Width,
                'height' => $this->Height,
                'document' => $this->Document->toArray()
            ];
        }

        /**
         * Constructs object from an array representation of the object
         *
         * @param array $data
         * @return DisplayPictureSize
         */
        public static function fromArray(array $data): DisplayPictureSize
        {
            $displayPictureObject = new DisplayPictureSize();

            if(isset($data['width']))
                $displayPictureObject->Width = $data['width'];

            if(isset($data['height']))
                $displayPictureObject->Height = $data['height'];

            if(isset($data['document']))
                $displayPictureObject->Document = Document::fromArray($data['document']);

            return $displayPictureObject;
        }

        /**
         * @inheritDoc
         */
        public static function getName(): string
        {
            return 'DisplayPictureSize';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'This object describes the size of a display picture followed by a document object that results in said display picture size.';
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
                new ParameterDefinition('width', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The width of the image'),

                new ParameterDefinition('height', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The height of the image'),

                new ParameterDefinition('height', [
                    new TypeDefinition(Document::getName(), false)
                ], true, 'The document object that points to the display picture'),
            ];
        }
    }
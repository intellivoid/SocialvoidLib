<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard\Profile;

    use SocialvoidLib\Objects\Standard\Document;

    class DisplayPictureSize
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
    }
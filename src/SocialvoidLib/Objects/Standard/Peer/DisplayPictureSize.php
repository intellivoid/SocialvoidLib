<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard\Peer;

    use SocialvoidLib\Objects\Standard\Document;

    class DisplayPictureSize
    {
        /**
         * The size of the profile picture
         *
         * @var string
         */
        public $Size;

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
                'size' => $this->Size,
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

            if(isset($data['size']))
                $displayPictureObject->Size = $data['size'];

            if(isset($data['document']))
                $displayPictureObject->Document = Document::fromArray($data['document']);

            return $displayPictureObject;
        }
    }
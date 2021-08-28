<?php

    /** @noinspection PhpMissingFieldTypeInspection */
    
    namespace SocialvoidLib\Objects\Document;

    use SocialvoidLib\Abstracts\Types\DocumentType;

    class File
    {
        /**
         * The ID of the file, optional
         *
         * @var string|null
         */
        public $ID;

        /**
         * The file mime (File type)
         *
         * @var string
         */
        public $Mime;

        /**
         * The size of the file in bytes
         *
         * @var int
         */
        public $Size;

        /**
         * The file name
         *
         * @var string
         */
        public $Name;

        /**
         * The crc32 hash of the file
         *
         * @var string
         */
        public $Hash;

        /**
         * @var int|DocumentType
         */
        public $Type;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'id' => $this->ID,
                'mime' => $this->Mime,
                'size' => $this->Size,
                'name' => $this->Name,
                'hash' => $this->Hash,
                'type' => $this->Hash
            ];
        }

        /**
         * Constructs object from an array representation of the object
         *
         * @param array $data
         * @return File
         */
        public static function fromArray(array $data): File
        {
            $file_object = new File();

            if(isset($data['id']))
                $file_object->ID = $data['id'];

            if(isset($data['mime']))
                $file_object->Mime = $data['mime'];

            if(isset($data['size']))
                $file_object->Size = $data['size'];

            if(isset($data['name']))
                $file_object->Name = $data['name'];

            if(isset($data['hash']))
                $file_object->Hash = $data['hash'];

            if(isset($data['type']))
                $file_object->Type = $data['type'];

            return $file_object;
        }

    }
<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Document;

    use MimeLib\Exceptions\CannotDetectFileTypeException;
    use MimeLib\Exceptions\FileNotFoundException;
    use SocialvoidLib\Abstracts\Types\Standard\DocumentType;
    use SocialvoidLib\Classes\Validate;

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
         * @var string|DocumentType
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
                'type' => $this->Type
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

        /**
         * Creates a file object from a file
         *
         * @param string $path
         * @return File
         * @throws CannotDetectFileTypeException
         * @throws FileNotFoundException
         */
        public static function fromFile(string $path): File
        {
            $file_validation = Validate::validateFileInformation($path);
            $file_object = new File();

            $file_object->Hash = $file_validation->Hash;
            $file_object->Name = $file_validation->Name;
            $file_object->Mime = $file_validation->Mime;
            $file_object->Size = $file_validation->Size;
            $file_object->Type = $file_validation->FileType;

            return $file_object;
        }
    }
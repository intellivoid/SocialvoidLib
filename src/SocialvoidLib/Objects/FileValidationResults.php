<?php

    namespace SocialvoidLib\Objects;

    use SocialvoidLib\Abstracts\Types\DocumentType;

    class FileValidationResults
    {
        /**
         * The detected mime of the file
         *
         * @var string
         */
        public $Mime;

        /**
         * The file type for display purposes
         *
         * @var int|DocumentType
         */
        public $FileType;

        /**
         * The size of the file in bytes
         *
         * @var string
         */
        public $Size;

        /**
         * The name of the file, including the extension
         *
         * @var string
         */
        public $Name;

        /**
         * The SHA256 checksum of the file
         *
         * @var string
         */
        public $Hash;

        /**
         * @return array
         */
        public function toArray(): array
        {
            return [
                'mime' => $this->Mime,
                'file_type' => $this->FileType,
                'size' => $this->Size,
                'name' => $this->Name,
                'hash' => $this->Hash
            ];
        }

        public static function fromArray(array $data): FileValidationResults
        {
            $return_object = new FileValidationResults();

            if(isset($data['mime']))
                $return_object->Mime = $data['mime'];

            if(isset($data['file_type']))
                $return_object->FileType = $data['file_type'];

            if(isset($data['size']))
                $return_object->Size = $data['size'];

            if(isset($data['name']))
                $return_object->Name = $data['name'];

            if(isset($data['hash']))
                $return_object->Hash = $data['hash'];

            return $return_object;
        }
    }
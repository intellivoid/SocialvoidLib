<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Types\Standard\DocumentType;
    use SocialvoidLib\Objects\ContentResults;

    class Document
    {
        /**
         * The ID of the document
         *
         * @var string
         */
        public $ID;

        /**
         * The detected mime of the file
         *
         * @var string
         */
        public $FileMime;

        /**
         * The size of the file in bytes
         *
         * @var int
         */
        public $FileSize;

        /**
         * The name of the file
         *
         * @var string
         */
        public $FileName;

        /**
         * The file type for how the client should treat the file as
         *
         * @var DocumentType|string
         */
        public $FileType;

        /**
         * An array of flags associated with this document
         *
         * @var array
         */
        public $Flags;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'id' => $this->ID,
                'file_mime' => $this->FileMime,
                'file_name' => $this->FileName,
                'file_size' => $this->FileSize,
                'file_type' => $this->FileType,
                'flags' => $this->Flags
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return Document
         */
        public static function fromArray(array $data): Document
        {
            $document_object = new Document();

            if(isset($data['id']))
                $document_object->ID = $data['id'];

            if(isset($data['file_mime']))
                $document_object->FileMime = $data['file_mime'];

            if(isset($data['file_name']))
                $document_object->FileName = $data['file_name'];

            if(isset($data['file_type']))
                $document_object->FileType = $data['file_type'];

            if(isset($data['file_size']))
                $document_object->FileSize = $data['file_size'];

            if(isset($data['flags']))
                $document_object->Flags = $data['flags'];

            return $document_object;
        }

        /**
         * Constructs standard object from internal object
         *
         * @param \SocialvoidLib\Objects\Document $document
         * @param string $file_hash
         * @return Document
         */
        public static function fromDocument(\SocialvoidLib\Objects\Document $document, string $file_hash): Document
        {
            $document_object = new Document();
            $file = $document->getFile($file_hash);

            $document_object->ID = $document->ID . '-' . $file_hash;
            $document_object->Flags = $document->Flags;

            if($file !== null)
            {
                $document_object->FileMime = $file->Mime;
                $document_object->FileName = $file->Name;
                $document_object->FileType = $file->Type;
                $document_object->FileSize = $file->Size;
            }

            return $document_object;
        }

        /**
         * Constructs object from an internal ContentResults object to a standard object
         *
         * @param ContentResults $contentResults
         * @return Document
         */
        public static function fromContentResults(ContentResults $contentResults): Document
        {
            $document_object = new Document();

            $document_object->ID = $contentResults->DocumentID . '-' . $contentResults->FileHash;
            $document_object->Flags = $contentResults->Flags;
            $document_object->FileMime = $contentResults->FileMime;
            $document_object->FileType = $contentResults->FileType;
            $document_object->FileSize = $contentResults->FileSize;
            $document_object->FileName = $contentResults->FileName;

            return $document_object;
        }
    }
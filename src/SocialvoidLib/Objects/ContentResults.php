<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects;

    use SocialvoidLib\Abstracts\ContentSource;
    use SocialvoidLib\Abstracts\Types\FetchLocationType;

    class ContentResults
    {
        /**
         * @var string|FetchLocationType
         */
        public $FetchLocationType;

        /**
         * @var string|ContentSource
         */
        public $ContentSource;

        /**
         * @var string|null
         */
        public $ContentIdentifier;

        /**
         * @var string|null
         */
        public $DocumentID;

        /**
         * The location of the content
         *
         * @var string|null|mixed
         */
        public $Location;

        /**
         * @var string|null
         */
        public $FileID;

        /**
         * @var string|null
         */
        public $FileHash;

        /**
         * The file mime
         *
         * @var string|null
         */
        public $FileMime;

        /**
         * The name of the file
         *
         * @var string|null
         */
        public $FileName;

        /**
         * The size of the file
         *
         * @var string|null
         */
        public $FileSize;

        /**
         * The file type
         *
         * @var string
         */
        public $FileType;

        /**
         * Array of flags associated with this file
         *
         * @var array
         */
        public $Flags;

        /**
         * The Unix Timestamp for when this record was created
         *
         * @var int
         */
        public $CreatedTimestamp;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'fetch_location_type' => $this->FetchLocationType,
                'content_source' => $this->ContentSource,
                'content_identifier' => $this->ContentIdentifier,
                'document_id' => $this->DocumentID,
                'location' => $this->Location,
                'file_id' => $this->FileID,
                'file_hash' => $this->FileHash,
                'file_mime' => $this->FileMime,
                'file_name' => $this->FileName,
                'file_size' => $this->FileSize,
                'file_type' => $this->FileType,
                'flags' => $this->Flags,
                'created_timestamp' => $this->CreatedTimestamp
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return ContentResults
         */
        public static function fromArray(array $data): ContentResults
        {
            $content_results_object = new ContentResults();

            if(isset($data['fetch_location_type']))
                $content_results_object->FetchLocationType = $data['fetch_location_type'];

            if(isset($data['content_source']))
                $content_results_object->ContentSource = $data['content_source'];

            if(isset($data['content_identifier']))
                $content_results_object->ContentIdentifier = $data['content_identifier'];

            if(isset($data['document_id']))
                $content_results_object->DocumentID = $data['document_id'];

            if(isset($data['location']))
                $content_results_object->Location = $data['location'];

            if(isset($data['file_id']))
                $content_results_object->FIleID = $data['file_id'];

            if(isset($data['file_hash']))
                $content_results_object->FileHash = $data['file_hash'];

            if(isset($data['file_mime']))
                $content_results_object->FileMime = $data['file_mime'];

            if(isset($data['file_name']))
                $content_results_object->FileName = $data['file_name'];

            if(isset($data['file_size']))
                $content_results_object->FileSize = $data['file_size'];

            /** @noinspection DuplicatedCode */
            if(isset($data['file_type']))
                $content_results_object->FileType = $data['file_type'];

            if(isset($data['flags']))
                $content_results_object->Flags = $data['flags'];

            if(isset($data['created_timestamp']))
                $content_results_object->CreatedTimestamp = $data['created_timestamp'];

            return $content_results_object;
        }
    }
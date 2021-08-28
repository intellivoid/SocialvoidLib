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
         * The location of the content
         *
         * @var string|null|mixed
         */
        public $Location;

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
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'fetch_location_type' => $this->FetchLocationType,
                'content_source' => $this->ContentSource,
                'location' => $this->Location,
                'file_mime' => $this->FileMime,
                'file_name' => $this->FileName,
                'file_size' => $this->FileSize
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

            if(isset($data['location']))
                $content_results_object->Location = $data['location'];

            if(isset($data['file_mime']))
                $content_results_object->FileMime = $data['file_mime'];

            if(isset($data['file_name']))
                $content_results_object->FileName = $data['file_name'];

            if(isset($data['file_size']))
                $content_results_object->FileSize = $data['file_size'];

            return $content_results_object;
        }
    }
<?php


    namespace SocialvoidLib\Objects\Post;

    use SocialvoidLib\Abstracts\Types\MediaType;

    /**
     * Class MediaContent
     * @package SocialvoidLib\Objects\Post
     */
    class MediaContent
    {
        /**
         * The provider that hosts this media content
         *
         * @var string
         */
        public $ProviderName;

        /**
         * The URL of the provider
         *
         * @var string|null
         */
        public $ProviderUrl;

        /**
         * The type of media that this content represents
         *
         * @var string|MediaType
         */
        public $MediaType;

        /**
         * The direct URL that shows the content
         *
         * @var string
         */
        public $URL;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "provider_name" => $this->ProviderName,
                "provider_url" => $this->ProviderUrl,
                "media_type" => $this->MediaType,
                "url" => $this->URL
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return MediaContent
         */
        public static function fromArray(array $data): MediaContent
        {
            $MediaContentObject = new MediaContent();

            if(isset($data["provider_name"]))
                $MediaContentObject->ProviderName = $data["provider_name"];

            if(isset($data["provider_url"]))
                $MediaContentObject->ProviderUrl = $data["provider_url"];

            if(isset($data["media_type"]))
                $MediaContentObject->MediaType = $data["media_type"];

            if(isset($data["url"]))
                $MediaContentObject->URL = $data["url"];

            return $MediaContentObject;
        }
    }
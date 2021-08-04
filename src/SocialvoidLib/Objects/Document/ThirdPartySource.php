<?php

    /** @noinspection PhpMissingFieldTypeInspection */


    namespace SocialvoidLib\Objects\Document;

        /**
     * Class ThirdPartySource
     * @package SocialvoidLib\Objects\Document
     */
    class ThirdPartySource
    {
        /**
         * The name of the third-party source, eg; "Twitter".
         * So the display would be: "Content provided by Twitter"
         *
         * @var string
         */
        public $Name;

        /**
         * The URL used to access the document in question
         *
         * @var string
         */
        public $AccessUrl;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "name" => $this->Name,
                "access_url" => $this->AccessUrl
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return ThirdPartySource
         */
        public static function fromArray(array $data): ThirdPartySource
        {
            $ThirdPartySourceObject = new ThirdPartySource();

            if(isset($data["name"]))
                $ThirdPartySourceObject->Name = $data["name"];

            if(isset($data["access_url"]))
                $ThirdPartySourceObject->AccessUrl = $data["access_url"];

            return $ThirdPartySourceObject;
        }
    }
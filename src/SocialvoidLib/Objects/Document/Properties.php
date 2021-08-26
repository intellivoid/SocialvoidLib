<?php


    namespace SocialvoidLib\Objects\Document;

    /**
     * Class Properties
     * @package SocialvoidLib\Objects\Document
     */
    class Properties
    {
        /**
         * @var array
         */
        public $AvailableImageSizes = [];

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'available_image_sizes' => $this->AvailableImageSizes
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return Properties
         */
        public static function fromArray(array $data): Properties
        {
            $properties = new Properties();

            if(isset($data['available_image_sizes']))
                $properties = $properties->AvailableImageSizes;

            return $properties;
        }
    }
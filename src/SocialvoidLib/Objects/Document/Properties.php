<?php


    namespace SocialvoidLib\Objects\Document;

    /**
     * Class Properties
     * @package SocialvoidLib\Objects\Document
     */
    class Properties
    {
        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return Properties
         */
        public static function fromArray(array $data): Properties
        {
            return new Properties();
        }
    }
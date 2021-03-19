<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */


    namespace SocialvoidLib\Objects;

    /**
     * Class CacheEntry
     * @package SocialvoidLib\Objects
     */
    class CacheEntry
    {
        /**
         * The unique ID that identifies this cache entry
         *
         * @var string
         */
        public $ID;

        /**
         * A unique identifiable object type that can be converted back to a object
         *
         * @var string
         */
        public $ObjectType;

        /**
         * The data of the object
         *
         * @var mixed
         */
        public $ObjectData;

        /**
         * The Unix Timestamp for when this cache entry was created
         *
         * @var int
         */
        public $CreatedTimestamp;

        /**
         * The Unix Timestamp for when this cache entry was last updated
         *
         * @var int
         */
        public $LastUpdatedTimestamp;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                0x001 => $this->ID,
                0x002 => $this->ObjectType,
                0x003 => $this->ObjectData,
                0x004 => $this->CreatedTimestamp,
                0x005 => $this->LastUpdatedTimestamp
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return CacheEntry
         */
        public static function fromArray(array $data): CacheEntry
        {
            $CacheEntryObject = new CacheEntry();

            if(isset($data[0x001]))
                $CacheEntryObject->ID = $data[0x001];

            if(isset($data[0x002]))
                $CacheEntryObject->ObjectType = $data[0x002];

            if(isset($data[0x003]))
                $CacheEntryObject->ObjectData = $data[0x003];

            if(isset($data[0x004]))
                $CacheEntryObject->CreatedTimestamp = $data[0x004];

            if(isset($data[0x005]))
                $CacheEntryObject->LastUpdatedTimestamp = $data[0x005];

            return $CacheEntryObject;
        }
    }
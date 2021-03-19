<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */


    namespace SocialvoidLib\Objects;

    /**
     * Class CacheEntryPointer
     * @package SocialvoidLib\Objects
     */
    class CacheEntryPointer
    {
        /**
         * The ID of the cache entry pointer
         *
         * @var string
         */
        public $ID;

        /**
         * The ID of the cache entry that this pointer points to
         *
         * @var string
         */
        public $CacheEntryID;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                0x001 => $this->ID,
                0x002 => $this->CacheEntryID
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return CacheEntryPointer
         */
        public function fromArray(array $data): CacheEntryPointer
        {
            $CacheEntryPointerObject = new CacheEntryPointer();

            if(isset($data[0x001]))
                $CacheEntryPointerObject->ID = $data[0x001];

            if(isset($data[0x002]))
                $CacheEntryPointerObject->CacheEntryID = $data[0x002];

            return $CacheEntryPointerObject;
        }
    }
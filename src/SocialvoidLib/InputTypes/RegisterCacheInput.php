<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\InputTypes;

    /**
     * Class RegisterCacheInput
     * @package SocialvoidLib\InputTypes
     */
    class RegisterCacheInput
    {
        /**
         * The name of the object
         *
         * @var string
         */
        public $ObjectType;

        /**
         * The data of the object
         *
         * @var array
         */
        public $ObjectData;

        /**
         * Values that can point to this object
         *
         * @var array
         */
        public $Pointers;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                0x001 => $this->ObjectType,
                0x002 => $this->ObjectData,
                0x003 => $this->Pointers
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return RegisterCacheInput
         */
        public static function fromArray(array $data): RegisterCacheInput
        {
            $RegisterCacheInputObject = new RegisterCacheInput();

            if(isset($data[0x001]))
                $RegisterCacheInputObject->ObjectType = $data[0x001];

            if(isset($data[0x002]))
                $RegisterCacheInputObject->ObjectData = $data[0x002];

            if(isset($data[0x003]))
                $RegisterCacheInputObject->Pointers = $data[0x003];

            return $RegisterCacheInputObject;
        }
    }
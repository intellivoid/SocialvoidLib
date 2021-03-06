<?php

    /** @noinspection PhpMissingFieldTypeInspection */
    /** @noinspection PhpUnused */

    namespace SocialvoidLib\InputTypes;


    /**
     * Class SessionDevice
     * @package SocialvoidLib\InputTypes
     */
    class SessionDevice
    {
        /**
         * The model of the device that's used for this session
         *
         * @var string|null
         */
        public $DeviceModel;

        /**
         * The target platform that the client is running on
         *
         * @var string|null
         */
        public $Platform;

        /**
         * The system version being used
         *
         * @var string|null
         */
        public $SystemVersion;

        /**
         * SessionDevice constructor.
         * @param string|null $device_model
         * @param string|null $platform
         * @param string|null $system_version
         */
        public function __construct(string $device_model=null, string $platform=null, string $system_version=null)
        {
            $this->DeviceModel = $device_model;
            $this->Platform = $platform;
            $this->SystemVersion = $system_version;
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "device_model" => $this->DeviceModel,
                "platform" => $this->Platform,
                "system_version" => $this->SystemVersion,
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return SessionDevice
         */
        public static function fromArray(array $data): SessionDevice
        {
            $SessionDeviceObject = new SessionDevice();

            if(isset($data["device_model"]))
                $SessionDeviceObject->DeviceModel = $data["device_model"];

            if(isset($data["platform"]))
                $SessionDeviceObject->Platform = $data["platform"];

            if(isset($data["system_version"]))
                $SessionDeviceObject->SystemVersion = $data["system_version"];

            return $SessionDeviceObject;
        }
    }
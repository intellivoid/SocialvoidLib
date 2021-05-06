<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Objects;

    use udp\Abstracts\ImageType;

    /**
     * Class ImageDetails
     * @package SocialvoidLib\Objects
     */
    class ImageDetails
    {
        /**
         * @var int
         */
        public $Width;

        /**
         * @var int
         */
        public $Height;

        /**
         * @var ImageType
         */
        public $ImageType;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "width" => $this->Width,
                "height" => $this->Height,
                "image_type" => $this->ImageType
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return ImageDetails
         */
        public static function fromArray(array $data): ImageDetails
        {
            $ImageDetailsObject = new ImageDetails();

            if(isset($data["width"]))
                $ImageDetailsObject->Width = $data["width"];

            if(isset($data["height"]))
                $ImageDetailsObject->Height = $data["height"];

            if(isset($data["image_type"]))
                $ImageDetailsObject->ImageType = $data["image_type"];

            return $ImageDetailsObject;
        }
    }
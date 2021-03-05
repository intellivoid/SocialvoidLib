<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\User;

    use SocialvoidLib\Abstracts\CoaAvatarSize;

    /**
     * Class CoaAvatar
     * @package SocialvoidLib\Objects\User
     */
    class CoaAvatar
    {
        /**
         * The size of the avatar can be "original", "normal", "preview", "small" or "tiny"
         *
         * @var CoaAvatarSize
         */
        public $Size;

        /**
         * The URL of the avatar
         *
         * @var string
         */
        public $URL;

        /**
         * Returns an array representation of this object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "size" => $this->Size,
                "url" => $this->URL
            ];
        }

        /**
         * Constructs object from an arrays
         *
         * @param array $data
         * @return CoaAvatar
         */
        public static function fromArray(array $data): CoaAvatar
        {
            $CoaAvatarObject = new CoaAvatar();

            if(isset($data["size"]))
                $CoaAvatarObject->Size = $data["size"];

            if(isset($data["url"]))
                $CoaAvatarObject->URL = $data["url"];

            return $CoaAvatarObject;
        }
    }
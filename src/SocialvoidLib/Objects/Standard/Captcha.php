<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\StatusStates\CaptchaState;
    use SocialvoidLib\Abstracts\Types\Standard\CaptchaType;

    class Captcha
    {
        /**
         * The ID of the captcha
         *
         * @var string
         */
        public $ID;

        /**
         * The captcha type that indicates to the client how it's supposed to represent it to the user
         *
         * @var string|CaptchaType
         */
        public $Type;

        /**
         * The value of the captcha
         *
         * @var string
         */
        public $Value;

        /**
         * The state of the captcha
         *
         * @var string|CaptchaState
         */
        public $State;

        /**
         * The Unix Timestamp for when this captcha record expires
         *
         * @var int
         */
        public $Expires;

        /**
         * Constructs standard object from an internal object
         *
         * @param \SocialvoidLib\Objects\Captcha $captcha
         * @return Captcha
         */
        public static function fromCaptchaInternal(\SocialvoidLib\Objects\Captcha $captcha): Captcha
        {
            $captcha_object = new Captcha();
            $captcha->sync();

            $captcha_object->ID = $captcha->ID;
            $captcha_object->Value = $captcha->Value;
            $captcha_object->Expires = $captcha->ExpiryTimestamp;
            $captcha_object->Type = $captcha->Type;
            $captcha_object->State = $captcha->State;

            return $captcha_object;
        }

        /**
         * Returns an array representation of the object
         * @noinspection PhpCastIsUnnecessaryInspection
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'id' => $this->ID,
                'type' => $this->Type,
                'value' => $this->Value,
                'state' => $this->State,
                'expires' => (int)$this->Expires
            ];
        }

        /**
         * Constructs captcha object from an array representation
         *
         * @param array $data
         * @return Captcha
         * @noinspection DuplicatedCode
         */
        public static function fromArray(array $data): Captcha
        {
            $CaptchaObject = new Captcha();

            if(isset($data['id']))
                $CaptchaObject->ID = $data['id'];

            if(isset($data['type']))
                $CaptchaObject->Type = $data['type'];

            if(isset($data['value']))
                $CaptchaObject->Value = $data['value'];

            if(isset($data['state']))
                $CaptchaObject->State = $data['state'];

            if(isset($data['expires']))
                $CaptchaObject->Expires = (int)$data['expires'];

            return $CaptchaObject;
        }
    }
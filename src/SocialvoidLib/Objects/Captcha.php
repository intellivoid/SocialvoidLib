<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects;

    use SocialvoidLib\Abstracts\StatusStates\CaptchaState;
    use SocialvoidLib\Abstracts\Types\Standard\CaptchaType;

    class Captcha
    {
        /**
         * The ID of the captcha record
         *
         * @var string
         */
        public $ID;

        /**
         * The captcha type
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
         * The answer of the captcha
         *
         * @var string
         */
        public $Answer;

        /**
         * The state of the captcha
         *
         * @var string|CaptchaState
         */
        public $State;

        /**
         * Indicates if the captcha has been used or not
         *
         * @var bool
         */
        public $Used;

        /**
         * Additional data for the captcha
         *
         * @var array
         */
        public $Data;

        /**
         * The IP Address of the client that created the captcha
         *
         * @var string
         */
        public $IpAddress;

        /**
         * Indicates if the IP address is tied to the captcha or not
         *
         * @var bool
         */
        public $IpTied;

        /**
         * The Unix Timestamp for when the captcha expires
         *
         * @var int
         */
        public $ExpiryTimestamp;

        /**
         * The Unix Timestamp for when the captcha record was created
         *
         * @var int
         */
        public $CreatedTimestamp;

        /**
         * Syncs the current properties to be up-to-date
         */
        public function sync()
        {
            if(time() >= $this->ExpiryTimestamp)
                $this->State = CaptchaState::Expired;
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'id' => $this->ID,
                'type' => $this->Type,
                'value' => $this->Value,
                'answer' => $this->Answer,
                'state' => $this->State,
                'used' => $this->Used,
                'ip_address' => $this->IpAddress,
                'ip_tied' => $this->IpTied,
                'expiry_timestamp' => $this->ExpiryTimestamp,
                'created_timestamp' => $this->CreatedTimestamp
            ];
        }

        /**
         * Constructs object from an array representation
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

            if(isset($data['answer']))
                $CaptchaObject->Answer = $data['answer'];

            if(isset($data['state']))
                $CaptchaObject->State = $data['state'];

            if(isset($data['used']))
                $CaptchaObject->Used = (bool)$data['used'];

            if(isset($data['ip_address']))
                $CaptchaObject->IpAddress = $data['ip_address'];

            if(isset($data['ip_tied']))
                $CaptchaObject->IpTied = (bool)$data['ip_tied'];

            if(isset($data['expiry_timestamp']))
                $CaptchaObject->ExpiryTimestamp = (int)$data['expiry_timestamp'];

            if(isset($data['created_timestamp']))
                $CaptchaObject->CreatedTimestamp = (int)$data['created_timestamp'];

            $CaptchaObject->sync();
            return $CaptchaObject;
        }
    }
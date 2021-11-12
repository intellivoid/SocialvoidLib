<?php

    namespace SocialvoidLib\Classes\Captcha;

    use SocialvoidLib\Interfaces\CaptchaPhraseInterface;

    class PhraseBuilder implements CaptchaPhraseInterface
    {
        /**
         * @var int
         */
        public $length;

        /**
         * @var string
         */
        public $charset;

        /**
         * Constructs a PhraseBuilder with given parameters
         * @noinspection SpellCheckingInspection
         */
        public function __construct($length = 5, $charset = 'abcdefghijklmnpqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ')
        {
            $this->length = $length;
            $this->charset = $charset;
        }

        /**
         * Generates random phrase of given length with given charset
         *
         * @param null $length
         * @param null $charset
         * @return string
         * @noinspection PhpLoopCanBeReplacedWithStrRepeatInspection
         */
        public function build($length = null, $charset = null): string
        {
            if ($length !== null)
                $this->length = $length;
            if ($charset !== null)
                $this->charset = $charset;
            $phrase = '';
            $chars = str_split($this->charset);
            for ($i = 0; $i < $this->length; $i++)
                $phrase .= $chars[array_rand($chars)];
            return $phrase;
        }

        /**
         * "Niceize" a code
         *
         * @param $str
         * @return string
         */
        public function niceize($str): string
        {
            return self::doNiceize($str);
        }

        /**
         * A static helper to niceize
         *
         * @param $str
         * @return string
         */
        public static function doNiceize($str): string
        {
            return strtr(strtolower($str), '01', 'ol');
        }

        /**
         * A static helper to compare
         *
         * @param $str1
         * @param $str2
         * @return bool
         */
        public static function comparePhrases($str1, $str2): bool
        {
            return self::doNiceize($str1) === self::doNiceize($str2);
        }
    }
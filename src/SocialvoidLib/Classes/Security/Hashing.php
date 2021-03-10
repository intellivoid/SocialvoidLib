<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

namespace SocialvoidLib\Classes\Security;

    use tsa\Classes\Crypto;
    use tsa\Exceptions\BadLengthException;
    use tsa\Exceptions\SecuredRandomProcessorNotFoundException;

    /**
     * Class Hashing
     * @package SocialvoidLib\Classes\Security
     */
    class Hashing
    {
        /**
         * Peppers a data input into irreversible hash that is iterated randomly between
         * the minimum and maximum value of the given parameters
         *
         * @param string $data
         * @param int $min
         * @param int $max
         * @return string
         */
        public static function pepper(string $data, int $min = 100, int $max = 1000): string
        {
            $n = rand($min, $max);
            $res = '';
            $data = hash('whirlpool', $data);
            for ($i=0, $l=strlen($data) ; $l ; $l--)
            {
                $i = ($i+$n-1) % $l;
                $res = $res . $data[$i];
                $data = ($i ? substr($data, 0, $i) : '') . ($i < $l-1 ? substr($data, $i+1) : '');
            }
            return($res);
        }

        /**
         * Creates a random but secured recovery code using a timed-based secret signature
         * generator and a pepper algorithm
         *
         * @return string
         * @throws BadLengthException
         * @throws SecuredRandomProcessorNotFoundException
         */
        public static function generateRecoveryCode(): string
        {
            return hash('adler32', self::pepper(Crypto::BuildSecretSignature() . time()));
        }

        /**
         * Hashes a simple password using SHA512, SHA256 and HAVAL256,5
         *
         * @param string $password
         * @return string
         */
        public static function password(string $password): string
        {
            $first_part = hash("sha512", $password) .  hash("haval256,5", $password);
            $secondary_part = hash("sha256", $first_part);

            return $secondary_part . hash("haval256,5", $password);
        }
    }
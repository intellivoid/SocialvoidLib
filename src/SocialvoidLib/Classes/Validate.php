<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

namespace SocialvoidLib\Classes;

    use MimeLib\Exceptions\CannotDetectFileTypeException;
    use MimeLib\Exceptions\FileNotFoundException;
    use MimeLib\MimeLib;
    use SocialvoidLib\Abstracts\StandardErrorCodeType;
    use SocialvoidLib\Abstracts\Types\Standard\DocumentType;
    use SocialvoidLib\Objects\FileValidationResults;

    /**
     * Class Validation
     * @package SocialvoidLib\Classes
     */
    class Validate
    {
        /**
         * Validates if the given username is valid or not.
         *
         * @param string $input
         * @return bool
         */
        public static function username(string $input): bool
        {
            preg_match('/^(?!_)[A-Za-z0-9_]{3,32}(?<!_)$/m', $input, $matches);
            return count($matches) >= 1 && $matches !== false;
        }

        /**
         * Validates if the given password is safe or not.
         *
         * @param string $input
         * @return bool
         */
        public static function password(string $input): bool
        {
            /** @noinspection RegExpRedundantEscape */
            preg_match('/^(?=.*[A-Z])(?=.*\d.*\d)(?=.*[ -\/:-@\[-`\{-~])[ -~]{12,128}$/m', $input, $matches);
            return count($matches) >= 1 && $matches !== false;
        }

        /**
         * Validates the first name
         *
         * @param string|null $input
         * @return bool
         */
        public static function firstName(string $input=null): bool
        {
            if (strlen($input) == 0)
                return false;

            if (strlen($input) > 64)
                return false;

            return true;
        }

        /**
         * Validates the last name
         *
         * @param string|null $input
         * @return bool
         */
        public static function lastName(string $input=null): bool
        {
            if (strlen($input) > 64)
                return false;

            return true;
        }

        /**
         * Validates the user biography
         *
         * @param string|null $input
         * @return bool
         * @noinspection PhpUnused
         */
        public static function biography(string $input=null): bool
        {
            if(strlen($input) > 255)
                return false;

            return true;
        }

        /**
         * Determines the standard error code type by checking the error code range
         *
         * @param int $error_code
         * @return string
         */
        public static function determineStandardErrorType(int $error_code): string
        {
            /**
             * 40-Set error codes (Server)
             * 16384 - *
             */
            if($error_code >= 16384)
            {
                return StandardErrorCodeType::ServerError;
            }

            /**
             * 31-Set error codes (Network)
             * 12544 - 16383
             */
            if($error_code >= 12544)
            {
                return StandardErrorCodeType::NetworkError;
            }

            /**
             * 23-Set Error codes (Media)
             * 8960 - 12543
             */
            if($error_code >= 8960)
            {
                return StandardErrorCodeType::MediaError;
            }

            /**
             * 22-Set Error codes (Authentication)
             * 8704 - 8979
             */
            if($error_code >= 8704)
            {
                return StandardErrorCodeType::AuthenticationError;
            }

            /**
             * 21-Set Error codes (Validation)
             * 8448 - 8703
             */
            if($error_code >= 8448)
            {
                return StandardErrorCodeType::ValidationError;
            }

            return StandardErrorCodeType::Unknown;
        }

        /**
         * Determines if the error code is a standard to Socialvoid or not
         *
         * @param int $error_code
         * @return bool
         * @noinspection PhpUnused
         */
        public static function isStandardError(int $error_code): bool
        {
            if(self::determineStandardErrorType($error_code) == StandardErrorCodeType::Unknown)
            {
                return false;
            }

            return true;
        }

        /**
         * Validates information about a file
         *
         * @param string $file_path
         * @return FileValidationResults
         * @throws CannotDetectFileTypeException
         * @throws FileNotFoundException
         */
        public static function validateFileInformation(string $file_path): FileValidationResults
        {
            $results = new FileValidationResults();
            $results->Size = filesize($file_path);
            $results->Hash = hash_file('crc32', $file_path);
            $results->Mime = MimeLib::detectFileType($file_path)->getMime();
            $results->Name = basename($file_path);

            // Attempt to detect the file type
            if(stripos(strtolower($results->Mime), 'audio') !== false)
            {
                $results->FileType = DocumentType::Audio;
            }
            elseif(stripos(strtolower($results->Mime), 'image') !== false)
            {
                $results->FileType = DocumentType::Photo;
            }
            elseif(stripos(strtolower($results->Mime), 'video') !== false)
            {
                $results->FileType = DocumentType::Video;
            }
            else
            {
                $results->FileType = DocumentType::Document;
            }

            return $results;
        }

        /**
         * Validates the version number
         *
         * @param string $input
         * @return bool
         */
        public static function versionNumber(string $input): bool
        {
            return true;

            if($input == null)
            {
                return false;
            }

            if((bool)preg_match("/^([0-9]+)\.([0-9]+)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+[0-9A-Za-z-]+)?$/", $input))
            {
                return true;
            }

            if((bool)preg_match("/^([0-9]+)\.([0-9]+)\.([0-9]+)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+[0-9A-Za-z-]+)?$/", $input))
            {
                return true;
            }

            if((bool)preg_match("/^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+[0-9A-Za-z-]+)?$/", $input))
            {
                return true;
            }

            return false;
        }

        /**
         * Validates the representation of a hash
         *
         * @param string $input
         * @return bool
         */
        public static function hash(string $input): bool
        {
            return (ctype_xdigit($input) && strlen($input) % 2 == 0 && hex2bin($input));
        }
    }
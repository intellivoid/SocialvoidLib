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

    use SocialvoidLib\Abstracts\Types\DocumentType;
    use SocialvoidLib\Objects\ActiveSession;
    use SocialvoidLib\Objects\Document\File;
    use Zimage\Zimage;

    /**
     * Class Converter
     * @package SocialvoidLib\Classes
     */
    class Converter
    {
        /**
         * Converts an empty string to a null value, if empty.
         *
         * @param string|null $input
         * @return string|null
         */
        public static function emptyString(string $input=null): ?string
        {
            if($input == null)
                return null;

            if(strlen($input) == 0)
                return null;

            return $input;
        }

        /**
         * Adds a flag to the flag object
         *
         * @param array $flags
         * @param $flag
         */
        public static function addFlag(array &$flags, $flag): void
        {
            if(in_array($flag, $flags))
                return;

            $flags[] = $flag;
        }

        /**
         * Removes a flag from the flag object
         *
         * @param array $flags
         * @param $flag
         */
        public static function removeFlag(array &$flags, $flag): void
        {
            if(in_array($flag, $flags) == false)
                return;

            $flags = array_diff($flags, [$flag]);
        }

        /**
         * Determines if a flag set has a flag
         *
         * @param array $flags
         * @param mixed $flag
         * @return bool
         */
        public static function hasFlag(array &$flags, $flag): bool
        {
            if(is_array($flag))
            {
                foreach($flag as $value)
                {
                    if(in_array($value, $flags))
                        return true;
                }

                return false;
            }

            return in_array($flag, $flags);
        }

        /**
         * Attempts to recognize the source of a post
         *
         * @param ActiveSession $activeSession
         * @return string|null
         */
        public static function getSource(ActiveSession $activeSession): ?string
        {
            if($activeSession->Platform !== null && $activeSession->ClientName !== null)
                return $activeSession->ClientName . " (" . $activeSession->Platform . ")";

            if($activeSession->ClientName !== null)
                return $activeSession->ClientName;

            if($activeSession->Platform !== null)
                return $activeSession->Platform;

            return null;
        }

        /**
         * Converts the text to lowercase and replaces spaces with underscores
         *
         * @param string $input
         * @return string
         */
        public static function normalizeText(string $input): string
        {
            return str_ireplace(" ", "_", strtolower($input));
        }

        /**
         * Converts a Zimage object to a array of Document files
         *
         * @param Zimage $zimage
         * @param string $image_name
         * @return File[]
         */
        public static function zimageToFiles(Zimage $zimage, string $image_name): array
        {
            $document_files = [];

            foreach($zimage->getImages() as $image)
            {
                $document_file = new File();
                $document_file->Mime = 'image/jpeg';
                $document_file->Hash = hash('crc32', $image->getData());
                $document_file->Size = strlen($image->getSize());
                $document_file->Name = $image_name . (string)$image->getSize() . '.jpg';
                $document_file->ID = $image->getSize();
                $document_file->Type = DocumentType::Photo;

                $document_files[] = $document_file;
            }

            return $document_files;
        }
    }
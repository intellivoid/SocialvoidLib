<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects;

    /**
     * Class TelegramCdnUploadRecord
     * @package SocialvoidLib\Objects
     */
    class TelegramCdnUploadRecord
    {
        /**
         * The Unique Internal Database ID for this file
         *
         * @var int
         */
        public $ID;

        /**
         * The Public ID of the file
         *
         * @var string
         */
        public $PublicID;

        /**
         * Identifier for this file, which can be used to download or reuse the file
         *
         * @var string
         */
        public $FileID;

        /**
         * Unique identifier for this file, which is supposed to be the same over time and for different bots. Can't be used to download or reuse the file.
         *
         * @var string
         */
        public $FileUniqueID;

        /**
         * MIME type of the file as defined by sender
         *
         * @var int
         */
        public $MimeType;

        /**
         * File size on the CDN
         *
         * @var int|null
         */
        public $CdnFileSize;

        /**
         * The hash of the file stored on the CDN
         *
         * @var string
         */
        public $CdnFileHash;

        /**
         * The original file size
         *
         * @var int|null
         */
        public $OriginalFileSize;

        /**
         * The original file hash
         *
         * @var string
         */
        public $OriginalFileHash;

        /**
         * The encryption key used to encrypt the contents of the file
         *
         * @var string
         */
        public $EncryptionKey;

        /**
         * The temporary access URL that is given by the CDN
         *
         * @var string|null
         */
        public $AccessUrl;

        /**
         * The Unix Timestamp for when the Access URL is supposed to expire
         *
         * @var int|null
         */
        public $AccessUrlExpiryTimestamp;

        /**
         * The Unix Timestamp for when this record was created
         *
         * @var int|null
         */
        public $CreatedTimestamp;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "id" => $this->ID,
                "public_id" => $this->PublicID,
                "file_id" => $this->FileID,
                "file_unique_id" => $this->FileUniqueID,
                "mime_type" => $this->MimeType,
                "cdn_file_size" => $this->CdnFileSize,
                "cdn_file_hash" => $this->CdnFileHash,
                "original_file_size" => $this->OriginalFileSize,
                "original_file_hash" => $this->OriginalFileHash,
                "encryption_key" => $this->EncryptionKey,
                "access_url" => $this->AccessUrl,
                "access_url_expiry_timestamp" => $this->AccessUrlExpiryTimestamp,
                "created_timestamp" => $this->CreatedTimestamp
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return TelegramCdnUploadRecord
         */
        public static function fromArray(array $data): TelegramCdnUploadRecord
        {
            $TelegramCdnUploadRecordObject = new TelegramCdnUploadRecord();

            if(isset($data["id"]))
                $TelegramCdnUploadRecordObject->ID = $data["id"];

            if(isset($data["public_id"]))
                $TelegramCdnUploadRecordObject->PublicID = $data["public_id"];

            if(isset($data["file_id"]))
                $TelegramCdnUploadRecordObject->FileID = $data["file_id"];

            if(isset($data["file_unique_id"]))
                $TelegramCdnUploadRecordObject->FileUniqueID = $data["file_unique_id"];

            if(isset($data["mime_type"]))
                $TelegramCdnUploadRecordObject->MimeType = $data["mime_type"];

            if(isset($data["cdn_file_size"]))
                $TelegramCdnUploadRecordObject->CdnFileSize = $data["cdn_file_size"];

            if(isset($data["cdn_file_hash"]))
                $TelegramCdnUploadRecordObject->CdnFileHash = $data["cdn_file_hash"];

            if(isset($data["original_file_size"]))
                $TelegramCdnUploadRecordObject->OriginalFileSize = $data["original_file_size"];

            if(isset($data["original_file_hash"]))
                $TelegramCdnUploadRecordObject->OriginalFileHash = $data["original_file_hash"];

            if(isset($data["encryption_key"]))
                $TelegramCdnUploadRecordObject->EncryptionKey = $data["encryption_key"];

            if(isset($data["access_url"]))
                $TelegramCdnUploadRecordObject->AccessUrl = $data["access_url"];

            if(isset($data["access_url_expiry_timestamp"]))
                $TelegramCdnUploadRecordObject->AccessUrlExpiryTimestamp = (int)$data["access_url_expiry_timestamp"];

            if(isset($data["created_timestamp"]))
                $TelegramCdnUploadRecordObject->CreatedTimestamp = (int)$data["created_timestamp"];

            return $TelegramCdnUploadRecordObject;
        }
    }
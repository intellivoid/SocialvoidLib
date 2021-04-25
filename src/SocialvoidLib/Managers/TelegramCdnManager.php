<?php

    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Managers;

    use msqg\QueryBuilder;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\Internal\CdnFileNotFoundException;
    use SocialvoidLib\Exceptions\Internal\FileTooLargeException;
    use SocialvoidLib\Objects\TelegramCdnUploadRecord;
    use SocialvoidLib\SocialvoidLib;
    use TelegramCDN\Exceptions\FileSecurityException;
    use TelegramCDN\Exceptions\UploadError;
    use TelegramCDN\Objects\EncryptedFile;
    use TelegramCDN\TelegramCDN;

    /**
     * Class TelegramCdnManager
     * @package SocialvoidLib\Managers
     */
    class TelegramCdnManager
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * @var TelegramCDN
         */
        private TelegramCDN $cdn;

        /**
         * TelegramCdnManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
            $this->cdn = new TelegramCDN(
                $socialvoidLib->getTelegramCdnConfiguration()["BotToken"],
                $socialvoidLib->getTelegramCdnConfiguration()["Channels"]
            );
        }

        /**
         * Uploads a file to the Telegram CDN
         *
         * @param string $file_path
         * @param string|null $unique_identifier
         * @return string
         * @throws DatabaseException
         * @throws FileTooLargeException
         * @throws UploadError
         */
        public function uploadContent(string $file_path, ?string $unique_identifier=null): string
        {
            $file_contents = file_get_contents($file_path);
            if(strlen($file_contents) > 15728640) // 15MB
                throw new FileTooLargeException("The maximum upload size is 15MB");

            $public_id = Utilities::generateTelegramCdnId($file_contents . (string)$unique_identifier);

            // If the file has already been uploaded, return the existing cdn id.
            if($this->fileHashExists($file_path))
                return $public_id;

            $upload_result = $this->cdn->uploadFile($file_path);

            $query = QueryBuilder::insert_into("telegram_cdn", [
                "public_id" => $this->socialvoidLib->getDatabase()->real_escape_string($public_id),
                "file_id" => $this->socialvoidLib->getDatabase()->real_escape_string($upload_result->FileID),
                "file_unique_id" => $this->socialvoidLib->getDatabase()->real_escape_string($upload_result->FileUniqueID),
                "mime_type" => $this->socialvoidLib->getDatabase()->real_escape_string($upload_result->MimeType),
                "cdn_file_size" => (int)$upload_result->CdnFileSize,
                "cdn_file_hash" => $this->socialvoidLib->getDatabase()->real_escape_string($upload_result->CdnFileHash),
                "original_file_size" => (int)$upload_result->OriginalFileSize,
                "original_file_hash" => $this->socialvoidLib->getDatabase()->real_escape_string($upload_result->OriginalFileHash),
                "encryption_key" => $this->socialvoidLib->getDatabase()->real_escape_string($upload_result->EncryptionKey),
                "created_timestamp" => (int)time()
            ]);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($query);
            if($QueryResults)
            {
                return $public_id;
            }
            else
            {
                throw new DatabaseException("There was an error while trying to upload the content",
                    $query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Returns an existing upload record from the database
         *
         * @param string $public_id
         * @return TelegramCdnUploadRecord
         * @throws CdnFileNotFoundException
         * @throws DatabaseException
         */
        public function getUploadRecord(string $public_id): TelegramCdnUploadRecord
        {
            $query = QueryBuilder::select("telegram_cdn", [
                "public_id",
                "file_id",
                "file_unique_id",
                "mime_type",
                "cdn_file_size",
                "cdn_file_hash",
                "original_file_size",
                "original_file_hash",
                "encryption_key",
                "access_url",
                "access_url_expiry_timestamp",
                "created_timestamp"
            ], "public_id", $public_id);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($query);

            if($QueryResults)
            {
                if ($QueryResults->num_rows == 0)
                {
                    throw new CdnFileNotFoundException("The requested file was not found in the database", $public_id);
                }

                $return_results = TelegramCdnUploadRecord::fromArray($QueryResults->fetch_array(MYSQLI_ASSOC));

                if($return_results->AccessUrlExpiryTimestamp == null || time() > $return_results->AccessUrlExpiryTimestamp)
                {
                    return $this->updateAccessURL($return_results);
                }

                return $return_results;
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying retrieve the file from the CDN",
                    $query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Updates the Access URL
         *
         * @param TelegramCdnUploadRecord $telegramCdnUploadRecord
         * @return TelegramCdnUploadRecord
         * @throws DatabaseException
         */
        public function updateAccessURL(TelegramCdnUploadRecord $telegramCdnUploadRecord): TelegramCdnUploadRecord
        {
            $telegramCdnUploadRecord->AccessUrl = $this->cdn->getFileUrl($telegramCdnUploadRecord->FileID);
            $telegramCdnUploadRecord->AccessUrlExpiryTimestamp = time() + 2700; // 45 Minutes

            $query = QueryBuilder::update("telegram_cdn", [
                "access_url" => $this->socialvoidLib->getDatabase()->real_escape_string($telegramCdnUploadRecord->AccessUrl),
                "access_url_expiry_timestamp" => $telegramCdnUploadRecord->AccessUrlExpiryTimestamp,
            ], "public_id", $telegramCdnUploadRecord->PublicID);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($query);

            if($QueryResults)
            {
                return $telegramCdnUploadRecord;
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying to update the Access URL",
                    $query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Downloads the file from a upload record and returns the file contents
         *
         * @param TelegramCdnUploadRecord $telegramCdnUploadRecord
         * @return string
         * @throws DatabaseException
         * @throws FileSecurityException
         */
        public function downloadFile(TelegramCdnUploadRecord $telegramCdnUploadRecord): string
        {
            if(
                $telegramCdnUploadRecord->AccessUrlExpiryTimestamp == null ||
                time() > $telegramCdnUploadRecord->AccessUrlExpiryTimestamp)
            {
                $telegramCdnUploadRecord = $this->updateAccessURL($telegramCdnUploadRecord);
            }

            return $this->cdn->decryptFile(EncryptedFile::fromArray($telegramCdnUploadRecord->toArray()),
                true, $telegramCdnUploadRecord->AccessUrl);
        }

        /**
         * Determines if the file has already been uploaded
         *
         * @param string $file_path
         * @return bool
         * @throws DatabaseException
         */
        private function fileHashExists(string $file_path): bool
        {
            $file_contents = file_get_contents($file_path);
            $query = QueryBuilder::select("telegram_cdn", [
                "public_id"
            ], "original_file_hash", hash("sha256", $file_contents));

            $QueryResults = $this->socialvoidLib->getDatabase()->query($query);

            if($QueryResults)
            {
                if ($QueryResults->num_rows == 0)
                {
                    return false;
                }

                return true;
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying execute the query",
                    $query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }
    }
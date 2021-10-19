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

    use Defuse\Crypto\Exception\BadFormatException;
    use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
    use Defuse\Crypto\Exception\IOException;
    use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
    use Exception;
    use Longman\TelegramBot\Exception\TelegramException;
    use MimeLib\Exceptions\CannotDetectFileTypeException;
    use MimeLib\Exceptions\FileNotFoundException;
    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\Types\CacheEntryObjectType;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\CacheMissedException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\DependencyError;
    use SocialvoidLib\Exceptions\GenericInternal\RedisCacheException;
    use SocialvoidLib\Exceptions\Internal\CdnFileNotFoundException;
    use SocialvoidLib\Exceptions\Internal\FileTooLargeException;
    use SocialvoidLib\InputTypes\RegisterCacheInput;
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
         * @throws TelegramException
         * @throws TelegramException
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
            $this->cdn = new TelegramCDN(
                $socialvoidLib->getCdnConfiguration()['TelegramBotToken'],
                $socialvoidLib->getCdnConfiguration()['TelegramChannels']
            );
        }

        /**
         * Uploads a file to the Telegram CDN
         *
         * @param string $file_path
         * @return string
         * @throws DatabaseException
         * @throws EnvironmentIsBrokenException
         * @throws FileTooLargeException
         * @throws IOException
         * @throws UploadError
         * @throws CannotDetectFileTypeException
         * @throws FileNotFoundException
         */
        public function uploadContent(string $file_path): string
        {
            if(filesize($file_path) > $this->socialvoidLib->getCdnConfiguration()['MaxFileUploadSize'])
                throw new FileTooLargeException('The maximum upload size is ' . $this->socialvoidLib->getCdnConfiguration()['MaxFileUploadSize'] . ' bytes');

            // If the file has already been uploaded, return the existing cdn id.
            $public_id = $this->fileHashExists($file_path);
            if($public_id !== null)
                return $public_id;

            // Generate a new one
            $public_id = Utilities::generateTelegramCdnId(hash_file('sha256', $file_path) . $file_path);
            $upload_result = $this->cdn->uploadFileEncrypted($file_path);

            $query = QueryBuilder::insert_into('telegram_cdn', [
                'public_id' => $this->socialvoidLib->getDatabase()->real_escape_string($public_id),
                'file_id' => $this->socialvoidLib->getDatabase()->real_escape_string($upload_result->FileID),
                'file_unique_id' => $this->socialvoidLib->getDatabase()->real_escape_string($upload_result->FileUniqueID),
                'mime_type' => $this->socialvoidLib->getDatabase()->real_escape_string($upload_result->MimeType),
                'cdn_file_size' => (int)$upload_result->CdnFileSize,
                'cdn_file_hash' => $this->socialvoidLib->getDatabase()->real_escape_string($upload_result->CdnFileHash),
                'original_file_size' => (int)$upload_result->OriginalFileSize,
                'original_file_hash' => $this->socialvoidLib->getDatabase()->real_escape_string($upload_result->OriginalFileHash),
                'encryption_key' => $this->socialvoidLib->getDatabase()->real_escape_string($upload_result->EncryptionKey),
                'created_timestamp' => time()
            ]);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($query);
            if($QueryResults)
            {
                return $public_id;
            }
            else
            {
                throw new DatabaseException('There was an error while trying to upload the content',
                    $query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }


        /**
         * Returns an existing upload record from the database
         *
         * @param string $public_id
         * @return TelegramCdnUploadRecord
         * @throws CacheException
         * @throws CdnFileNotFoundException
         * @throws DatabaseException
         * @throws TelegramException
         */
        public function getUploadRecord(string $public_id): TelegramCdnUploadRecord
        {
            if($this->socialvoidLib->getRedisBasicCacheConfiguration()["Enabled"] && $this->socialvoidLib->getRedisBasicCacheConfiguration()["TelegramCdnCacheEnabled"])
            {
                $CachedPost = $this->getObjectCacheEntry($public_id);
                if($CachedPost !== null)
                {
                    if($CachedPost->AccessUrlExpiryTimestamp == null || time() > $CachedPost->AccessUrlExpiryTimestamp)
                    {
                        return $this->updateAccessURL($CachedPost);
                    }

                    return $CachedPost;
                }
            }

            $query = QueryBuilder::select('telegram_cdn', [
                'public_id',
                'file_id',
                'file_unique_id',
                'mime_type',
                'cdn_file_size',
                'cdn_file_hash',
                'original_file_size',
                'original_file_hash',
                'encryption_key',
                'access_url',
                'access_url_expiry_timestamp',
                'created_timestamp'
            ], 'public_id', $public_id);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($query);

            if($QueryResults)
            {
                if ($QueryResults->num_rows == 0)
                    throw new CdnFileNotFoundException('The requested file was not found in the database', $public_id);

                $return_results = TelegramCdnUploadRecord::fromArray($QueryResults->fetch_array(MYSQLI_ASSOC));

                if($return_results->AccessUrlExpiryTimestamp == null || time() > $return_results->AccessUrlExpiryTimestamp)
                {
                    return $this->updateAccessURL($return_results);
                }

                $this->registerObjectCacheEntry($return_results);
                return $return_results;
            }
            else
            {
                throw new DatabaseException(
                    'There was an error while trying retrieve the file from the CDN',
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
         * @throws TelegramException
         * @throws CacheException
         */
        public function updateAccessURL(TelegramCdnUploadRecord $telegramCdnUploadRecord): TelegramCdnUploadRecord
        {
            $telegramCdnUploadRecord->AccessUrl = $this->cdn->getFileUrl($telegramCdnUploadRecord->FileID);
            $telegramCdnUploadRecord->AccessUrlExpiryTimestamp = time() + 2700; // 45 Minutes

            $query = QueryBuilder::update('telegram_cdn', [
                'access_url' => $this->socialvoidLib->getDatabase()->real_escape_string($telegramCdnUploadRecord->AccessUrl),
                'access_url_expiry_timestamp' => $telegramCdnUploadRecord->AccessUrlExpiryTimestamp,
            ], 'public_id', $telegramCdnUploadRecord->PublicID);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($query);

            if($QueryResults)
            {
                $this->registerObjectCacheEntry($telegramCdnUploadRecord);
                return $telegramCdnUploadRecord;
            }
            else
            {
                throw new DatabaseException(
                    'There was an error while trying to update the Access URL',
                    $query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Downloads the file from an upload record and returns the file contents
         *
         * @param TelegramCdnUploadRecord $telegramCdnUploadRecord
         * @return string
         * @throws DatabaseException
         * @throws EnvironmentIsBrokenException
         * @throws FileSecurityException
         * @throws TelegramException
         * @throws BadFormatException
         * @throws WrongKeyOrModifiedCiphertextException
         * @throws CacheException
         * @throws IOException
         */
        public function downloadFile(TelegramCdnUploadRecord $telegramCdnUploadRecord): string
        {
            if(
                $telegramCdnUploadRecord->AccessUrlExpiryTimestamp == null ||
                time() > $telegramCdnUploadRecord->AccessUrlExpiryTimestamp)
            {
                $telegramCdnUploadRecord = $this->updateAccessURL($telegramCdnUploadRecord);
            }

            return file_get_contents($this->cdn->downloadEncryptedFile(EncryptedFile::fromArray($telegramCdnUploadRecord->toArray()), true));
        }

        /**
         * Gets the download location
         *
         * @param TelegramCdnUploadRecord $telegramCdnUploadRecord
         * @return string
         * @throws CacheException
         * @throws DatabaseException
         * @throws TelegramException
         */
        public function getDownloadLocation(TelegramCdnUploadRecord $telegramCdnUploadRecord): string
        {
            if(
                $telegramCdnUploadRecord->AccessUrlExpiryTimestamp == null ||
                time() > $telegramCdnUploadRecord->AccessUrlExpiryTimestamp)
            {
                $telegramCdnUploadRecord = $this->updateAccessURL($telegramCdnUploadRecord);
            }

            return $telegramCdnUploadRecord->AccessUrl;
        }

        /**
         * Determines if the file has already been uploaded
         *
         * @param string $file_path
         * @return string|null
         * @throws DatabaseException
         */
        private function fileHashExists(string $file_path): ?string
        {
            $query = QueryBuilder::select('telegram_cdn', [
                'public_id'
            ], 'original_file_hash', hash_file('sha256', $file_path));

            $QueryResults = $this->socialvoidLib->getDatabase()->query($query);

            if($QueryResults)
            {
                if ($QueryResults->num_rows == 0)
                {
                    return null;
                }

                return $QueryResults->fetch_array(MYSQLI_ASSOC)['public_id'];
            }
            else
            {
                throw new DatabaseException(
                    'There was an error while trying execute the query',
                    $query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Registers a session cache entry
         *
         * @param TelegramCdnUploadRecord $uploadRecord
         * @throws CacheException
         */
        private function registerObjectCacheEntry(TelegramCdnUploadRecord $uploadRecord): void
        {
            if(
                $this->socialvoidLib->getRedisBasicCacheConfiguration()["Enabled"] &&
                $this->socialvoidLib->getRedisBasicCacheConfiguration()["TelegramCdnCacheEnabled"]
            )
            {
                $CacheEntryInput = new RegisterCacheInput();
                $CacheEntryInput->ObjectType = CacheEntryObjectType::TelegramCdnObject;
                $CacheEntryInput->ObjectData = $uploadRecord->toArray();
                $CacheEntryInput->Pointers = [$uploadRecord->ID, $uploadRecord->OriginalFileHash];

                try
                {
                    $this->socialvoidLib->getBasicRedisCacheManager()->registerCache(
                        $CacheEntryInput,
                        $this->socialvoidLib->getRedisBasicCacheConfiguration()["TelegramCdnCacheTTL"],
                        $this->socialvoidLib->getRedisBasicCacheConfiguration()["TelegramCdnCacheLimit"]
                    );
                }
                catch(Exception $e)
                {
                    throw new CacheException("There was an error while trying to register the cdn cache entry", 0, $e);
                }
            }
        }

        /**
         * Gets a cdn object cache entry
         *
         * @param string $value
         * @return TelegramCdnUploadRecord|null
         * @throws CacheException
         */
        private function getObjectCacheEntry(string $value): ?TelegramCdnUploadRecord
        {
            if($this->socialvoidLib->getRedisBasicCacheConfiguration()["Enabled"] == false)
                throw new CacheException("BasicRedisCache is not enabled");

            if($this->socialvoidLib->getRedisBasicCacheConfiguration()["TelegramCdnCacheEnabled"] == false)
                return null;

            try
            {
                $CacheEntryResults = $this->socialvoidLib->getBasicRedisCacheManager()->getCacheEntry(
                    CacheEntryObjectType::TelegramCdnObject, $value);
            }
            catch (CacheMissedException $e)
            {
                return null;
            }
            catch (DependencyError | RedisCacheException $e)
            {
                throw new CacheException("There was an issue while trying to request a cdn cache entry", 0, $e);
            }

            return TelegramCdnUploadRecord::fromArray($CacheEntryResults->ObjectData);
        }
    }
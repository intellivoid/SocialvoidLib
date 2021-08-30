<?php


    namespace SocialvoidLib\Managers;

    use SocialvoidLib\Classes\Converter;
    use SocialvoidLib\Exceptions\GenericInternal\CacheMissedException;
    use SocialvoidLib\Exceptions\GenericInternal\DependencyError;
    use SocialvoidLib\Exceptions\GenericInternal\RedisCacheException;
    use SocialvoidLib\InputTypes\RegisterCacheInput;
    use SocialvoidLib\Objects\CacheEntry;
    use SocialvoidLib\Objects\CacheEntryPointer;
    use SocialvoidLib\SocialvoidLib;
    use ZiProto\ZiProto;

    /**
     * Class BasicRedisCacheManager
     * @package SocialvoidLib\Managers
     */
    class BasicRedisCacheManager
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * BasicRedisCacheManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Registers a cache entry into the set
         *
         * @param RegisterCacheInput $registerCacheInput
         * @param int $ttl
         * @param int $limit
         * @throws DependencyError
         * @throws RedisCacheException
         */
        public function registerCache(RegisterCacheInput $registerCacheInput, int $ttl=0, int $limit=0): void
        {
            // Do not register the cache if the limit is reached
            // Ignore the limit rule if it's 0.

            $this->countRegisteredCacheEntries($registerCacheInput->ObjectType);
            if($limit > 0 && $this->countRegisteredCacheEntries($registerCacheInput->ObjectType) >= $limit)
                return;

            // Generate identification ID
            $CacheID = hash("crc32b", serialize($registerCacheInput->Pointers));

            $CacheEntryObject = new CacheEntry();
            $CacheEntryObject->ID = $CacheID;
            $CacheEntryObject->ObjectType = $registerCacheInput->ObjectType;
            $CacheEntryObject->ObjectData = $registerCacheInput->ObjectData;

            $CacheKeyEntry = str_ireplace(" ", ".", strtolower(
                $CacheEntryObject->ObjectType . ".i." . $CacheID)
            );

            foreach($registerCacheInput->Pointers as $pointer)
            {
                $CacheEntryPointer = new CacheEntryPointer();
                $CacheEntryPointer->PointerIdentifier = $pointer;
                $CacheEntryPointer->CacheEntryID = $CacheKeyEntry;

                $pointerData = ZiProto::encode($CacheEntryPointer->toArray());
                if($this->socialvoidLib->getRedisBasicCacheConfiguration()["UseCompression"])
                {
                    $pointerData = gzcompress($pointerData, $this->socialvoidLib->getRedisBasicCacheConfiguration()["CompressionLevel"]);
                }

                if(strlen($pointer) > 0)
                    $this->socialvoidLib->getBasicRedis()->set(
                        Converter::normalizeText($CacheEntryObject->ObjectType . "." . $pointer), $pointerData,
                        ['ex'=>$ttl]);
            }

            $entryData = ZiProto::encode($CacheEntryObject->toArray());
            if($this->socialvoidLib->getRedisBasicCacheConfiguration()["UseCompression"])
            {
                $entryData = gzcompress($entryData, $this->socialvoidLib->getRedisBasicCacheConfiguration()["CompressionLevel"]);
            }

            $this->socialvoidLib->getBasicRedis()->set($CacheKeyEntry, $entryData, ['ex'=>$ttl]);
        }

        /**
         * Returns an existing cache entry from a existing pointer
         *
         * @param string $object_type
         * @param string $pointer_value
         * @return CacheEntry
         * @throws CacheMissedException
         * @throws DependencyError
         * @throws RedisCacheException
         */
        public function getCacheEntry(string $object_type, string $pointer_value): CacheEntry
        {
            $CachePointerRequest = $this->socialvoidLib->getBasicRedis()->get($object_type . "." . $pointer_value);
            if($CachePointerRequest == false)
                throw new CacheMissedException("The requested cache request was a miss");

            if($this->socialvoidLib->getRedisBasicCacheConfiguration()["UseCompression"])
                $CachePointerRequest = gzuncompress($CachePointerRequest);

            $CacheEntryPointer = CacheEntryPointer::fromArray(ZiProto::decode($CachePointerRequest));
            $CacheEntryRequest = $this->socialvoidLib->getBasicRedis()->get($CacheEntryPointer->CacheEntryID);

            if($CacheEntryRequest == false)
                throw new CacheMissedException("The requested cache request was a miss");

            if($this->socialvoidLib->getRedisBasicCacheConfiguration()["UseCompression"])
                $CacheEntryRequest = gzuncompress($CacheEntryRequest);

            return CacheEntry::fromArray(ZiProto::decode($CacheEntryRequest));
        }

        /**
         * Counts the registered cache entries
         *
         * @param string $object_type
         * @return int
         * @throws DependencyError
         * @throws RedisCacheException
         */
        public function countRegisteredCacheEntries(string $object_type): int
        {
            return count($this->socialvoidLib->getBasicRedis()->keys($object_type . ".i.*"));
        }
    }
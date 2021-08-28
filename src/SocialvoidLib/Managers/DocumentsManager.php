<?php


    namespace SocialvoidLib\Managers;

    use Exception;
    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\Types\CacheEntryObjectType;
    use SocialvoidLib\Classes\Standard\BaseIdentification;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\CacheMissedException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\DependencyError;
    use SocialvoidLib\Exceptions\GenericInternal\RedisCacheException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\InputTypes\DocumentInput;
    use SocialvoidLib\InputTypes\RegisterCacheInput;
    use SocialvoidLib\Objects\Document;
    use SocialvoidLib\SocialvoidLib;
    use ZiProto\ZiProto;

    /**
     * Class DocumentsManager
     * @package SocialvoidLib\Managers
     */
    class DocumentsManager
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * DocumentsManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Creates a new document record in the database from the document input, returns the Document ID when
         * successful
         *
         * @param DocumentInput $documentInput
         * @return string
         * @throws DatabaseException
         */
        public function createDocument(DocumentInput $documentInput): string
        {
            $id = BaseIdentification::documentId($documentInput);

            $files = [];
            foreach($documentInput->Files as $file)
                $files[] = $file->toArray();

            $query = QueryBuilder::insert_into("documents", [
                'id' => $this->socialvoidLib->getDatabase()->real_escape_string($id),
                'content_source' => $this->socialvoidLib->getDatabase()->real_escape_string($documentInput->ContentSource),
                'content_identifier' => $this->socialvoidLib->getDatabase()->real_escape_string($documentInput->ContentIdentifier),
                'files' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($files)),
                'deleted' => (int)false,
                'owner_user_id' => $documentInput->OwnerUserID,
                'forward_user_id' => null,
                'access_type' => $this->socialvoidLib->getDatabase()->real_escape_string($documentInput->AccessType),
                'access_roles' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($documentInput->AccessRoles->toArray())),
                'flags' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                'properties' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($documentInput->Properties->toArray())),
                'last_accessed_timestamp' => null,
                'created_timestamp' => time()
            ]);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($query);
            if($QueryResults == false)
            {
                throw new DatabaseException("There was an error while trying to register document",
                    $query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }

            return $id;
        }

        /**
         * Retrieves the Document from the database
         *
         * @param string $document_id
         * @return Document
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         */
        public function getDocument(string $document_id): Document
        {
            $query = QueryBuilder::select('documents', [
                'id',
                'content_source',
                'content_identifier',
                'files',
                'deleted',
                'owner_user_id',
                'forward_user_id',
                'access_type',
                'access_roles',
                'flags',
                'properties',
                'last_accessed_timestamp',
                'created_timestamp'
            ], 'id', $this->socialvoidLib->getDatabase()->real_escape_string($document_id));

            $QueryResults = $this->socialvoidLib->getDatabase()->query($query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new DocumentNotFoundException('The requested document was not found in the network');
                }
                else
                {
                    $Row['files'] = ZiProto::decode($Row['files']);
                    $Row['access_roles'] = ZiProto::decode($Row['access_roles']);
                    $Row['flags'] = ZiProto::decode($Row['flags']);
                    $Row['properties'] = ZiProto::decode($Row['properties']);

                    return Document::fromArray($Row);
                }
            }
            else
            {
                throw new DatabaseException(
                    $this->socialvoidLib->getDatabase()->error,
                    $query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Updates the last accessed timestamp of the document
         *
         * @param Document $document
         * @throws DatabaseException
         */
        public function updateLastAccessTime(Document $document)
        {
            $query = QueryBuilder::update('documents', [
                'last_accessed_timestamp' => time()
            ], 'id', $document->ID);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($query);

            if($QueryResults == false)
            {
                throw new DatabaseException(
                    'There was an error while trying to update the document attribute',
                    $query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Marks the document as deleted
         *
         * @param Document $document
         * @throws DatabaseException
         */
        public function deleteDocument(Document $document)
        {
            /** @noinspection PhpBooleanCanBeSimplifiedInspection */
            $query = QueryBuilder::update('documents', [
                'deleted' => (int)false
            ], 'id', $document->ID);

            $QueryResults = $this->socialvoidLib->getDatabase()->query($query);

            if($QueryResults == false)
            {
                throw new DatabaseException(
                    'There was an error while trying to update the document attribute',
                    $query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Registers a document cache entry
         *
         * @param Document $document
         * @throws CacheException
         */
        private function registerDocumentCacheEntry(Document $document): void
        {
            if(
                $this->socialvoidLib->getRedisBasicCacheConfiguration()["Enabled"] &&
                $this->socialvoidLib->getRedisBasicCacheConfiguration()["DocumentCacheEnabled"]
            )
            {
                $CacheEntryInput = new RegisterCacheInput();
                $CacheEntryInput->ObjectType = CacheEntryObjectType::Document;
                $CacheEntryInput->ObjectData = $document->toArray();
                $CacheEntryInput->Pointers = [$document->ID];

                try
                {
                    $this->socialvoidLib->getBasicRedisCacheManager()->registerCache(
                        $CacheEntryInput,
                        $this->socialvoidLib->getRedisBasicCacheConfiguration()["DocumentCacheTTL"],
                        $this->socialvoidLib->getRedisBasicCacheConfiguration()["DocumentCacheLimit"]
                    );
                }
                catch(Exception $e)
                {
                    throw new CacheException("There was an error while trying to register the document cache entry", 0, $e);
                }
            }
        }

        /**
         * Gets a document cache entry
         *
         * @param string $value
         * @return Document|null
         * @throws CacheException
         */
        private function getSessionCacheEntry(string $value): ?Document
        {
            if($this->socialvoidLib->getRedisBasicCacheConfiguration()["Enabled"] == false)
                throw new CacheException("BasicRedisCache is not enabled");

            if($this->socialvoidLib->getRedisBasicCacheConfiguration()["DocumentCacheEnabled"] == false)
                return null;

            try
            {
                $CacheEntryResults = $this->socialvoidLib->getBasicRedisCacheManager()->getCacheEntry(
                    CacheEntryObjectType::Session, $value);
            }
            catch (CacheMissedException $e)
            {
                return null;
            }
            catch (DependencyError | RedisCacheException $e)
            {
                throw new CacheException("There was an issue while trying to request a session cache entry", 0, $e);
            }

            return Document::fromArray($CacheEntryResults->ObjectData);
        }

    }
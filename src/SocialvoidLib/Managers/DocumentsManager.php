<?php


    namespace SocialvoidLib\Managers;

    use MimeLib\Exceptions\CannotDetectFileTypeException;
    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\Types\Security\DocumentAccessType;
    use SocialvoidLib\Classes\Standard\BaseIdentification;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\FileNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\InputTypes\DocumentInput;
    use SocialvoidLib\Objects\AccessRoles;
    use SocialvoidLib\Objects\Document;
    use SocialvoidLib\Objects\User;
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
         * @param AccessRoles|null $accessRoles
         * @return string
         * @throws CannotDetectFileTypeException
         * @throws DatabaseException
         * @throws FileNotFoundException
         */
        public function createDocument(DocumentInput $documentInput): string
        {
            if(file_exists($documentInput->FilePath) == false)
                throw new FileNotFoundException('The file path in the document input was not found', $documentInput->FilePath);

            try
            {
                $file_validation = Validate::validateFileInformation($documentInput->FilePath);
            }
            catch (\MimeLib\Exceptions\FileNotFoundException $e)
            {
                throw new FileNotFoundException('The file path in the document input was not found', $documentInput->FilePath, null, $e);
            }

            $id = BaseIdentification::documentId($documentInput);
            $properties = new Document\Properties();

            $query = QueryBuilder::insert_into("documents", [
                'id' => $this->socialvoidLib->getDatabase()->real_escape_string($id),
                'content_source' => $this->socialvoidLib->getDatabase()->real_escape_string($documentInput->ContentSource),
                'content_identifier' => $this->socialvoidLib->getDatabase()->real_escape_string($documentInput->ContentIdentifier),
                'file_mime' => $this->socialvoidLib->getDatabase()->real_escape_string($file_validation->Mime),
                'file_size' => $this->socialvoidLib->getDatabase()->real_escape_string((int)$file_validation->Size),
                'file_name' => $this->socialvoidLib->getDatabase()->real_escape_string($file_validation->Name),
                'file_hash' => $this->socialvoidLib->getDatabase()->real_escape_string($file_validation->Hash),
                'document_type' => $this->socialvoidLib->getDatabase()->real_escape_string($file_validation->FileType),
                'deleted' => (int)false,
                'owner_user_id' => $documentInput->OwnerUserID,
                'forward_user_id' => null,
                'access_type' => $this->socialvoidLib->getDatabase()->real_escape_string($documentInput->AccessType),
                'access_roles' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($documentInput->AccessRoles->toArray())),
                'flags' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                'properties' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($properties->toArray())),
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
                'file_mime',
                'file_size',
                'file_hash',
                'document_type',
                'deleted',
                'owner_user_id',
                'forward_user_id',
                'access_type',
                'access_roles',
                'flags',
                'properties',
                'last_access_timestamp',
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
                    $Row['access_roles'] = ZiProto::decode($Row['access_roles']);
                    $Row['flags'] = ZiProto::decode($Row['flags']);
                    $Row['properties'] = ZiProto::decode($Row['properties']);

                    return Document::fromArray($Row);
                }
            }
            else
            {
                throw new DatabaseException(
                    'There was an error while trying to retrieve the Document',
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
    }
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
         * @param User $uploaderUser
         * @param AccessRoles|null $accessRoles
         * @return string
         * @throws CannotDetectFileTypeException
         * @throws DatabaseException
         * @throws FileNotFoundException
         */
        public function createDocument(DocumentInput $documentInput, User $uploaderUser, ?AccessRoles $accessRoles = null): string
        {
            // TODO: Validate object
            // TODO: Analyze the file before processing
            // TODO: Finish this function
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

            if($accessRoles == null)
                $accessRoles = new AccessRoles();

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
                'owner_user_id' => $uploaderUser->ID,
                'forward_user_id' => null,
                'access_type' => DocumentAccessType::Public,
                'access_roles' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($accessRoles->toArray())),
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

    }
<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects;

    use SocialvoidLib\Abstracts\Types\DocumentType;
    use SocialvoidLib\Abstracts\Types\Security\DocumentAccessType;
    use SocialvoidLib\Objects\Document\Properties;

    /**
     * Class Document
     * @package SocialvoidLib\Objects
     */
    class Document
    {
        /**
         * The ID of this record
         *
         * @var string
         */
        public $ID;

        /**
         * The source of the content
         *
         * @var string
         */
        public $ContentSource;

        /**
         * The unique identifier that points to the source of the content source
         *
         * @var string
         */
        public $ContentIdentifier;

        /**
         * The file mime (File type)
         *
         * @var string
         */
        public $FileMime;

        /**
         * The size of the file in bytes
         *
         * @var int
         */
        public $FileSize;

        /**
         * The file name
         *
         * @var string
         */
        public $FileName;

        /**
         * The file extension extracted from the file name
         *
         * @var string
         */
        public $FileHash;

        /**
         * @var int|DocumentType
         */
        public $Type;

        /**
         * Indicates if the document has been deleted or not
         *
         * @var bool
         */
        public $Deleted;

        /**
         * The User ID that originally created this document
         *
         * @var int
         */
        public $OwnerUserID;

        /**
         * The User ID that forwarded (made a copy) of this document
         *
         * @var int|null
         */
        public $ForwardUserID;

        /**
         * Indicates the access type for this document
         *
         * @var string|DocumentAccessType
         */
        public $AccessType;

        /**
         * AccessRoles object that indicates what entity has access to the document
         *
         * @var AccessRoles
         */
        public $AccessRoles;

        /**
         * Array of flags for this document
         *
         * @var array
         */
        public $Flags;

        /**
         * The properties object associated with this document
         *
         * @var Properties
         */
        public $Properties;

        /**
         * The Unix Timestamp for when this property was last accessed on the network
         *
         * @var int
         */
        public $LastAccessedTimestamp;

        /**
         * The Unix Timestamp for when this record was created
         *
         * @var int
         */
        public $CreatedTimestamp;

        /**
         * Document constructor.
         */
        public function __construct()
        {
            $this->AccessRoles = new AccessRoles();
            $this->Properties = new Properties();
            $this->Flags = [];
            $this->AccessType = DocumentAccessType::Protected;
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "id" => $this->ID,
                "content_source" => $this->ContentSource,
                "content_identifier" => $this->ContentIdentifier,
                "file_mime" => $this->FileMime,
                "file_size" => $this->FileSize,
                "file_name" => $this->FileName,
                "file_hash" => $this->FileHash,
                "document_type" => $this->Type,
                "deleted" => $this->Deleted,
                "owner_user_id" => $this->OwnerUserID,
                "forward_user_id" => $this->ForwardUserID,
                "access_type" => $this->AccessType,
                "access_roles" => $this->AccessRoles->toArray(),
                "flags" => $this->Flags,
                "properties" => $this->Properties->toArray(),
                "last_accessed_timestamp" => $this->LastAccessedTimestamp,
                "created_timestamp" => $this->CreatedTimestamp
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return Document
         */
        public static function fromArray(array $data): Document
        {
            $DocumentObject = new Document();

            if(Isset($data["id"]))
                $DocumentObject->ID = $data["id"];

            if(isset($data["content_source"]))
                $DocumentObject->ContentSource = $data["content_source"];

            if(isset($data["content_identifier"]))
                $DocumentObject->ContentIdentifier = $data["content_identifier"];

            if(isset($data["file_mime"]))
                $DocumentObject->FileMime = $data["file_mime"];

            if(isset($data["file_size"]))
                $DocumentObject->FileSize = $data["file_size"];

            if(isset($data["file_name"]))
                $DocumentObject->FileName = $data["file_name"];

            if(isset($data["file_hash"]))
                $DocumentObject->FileHash = $data["file_hash"];

            if(isset($data["document_type"]))
                $DocumentObject->Type = $data["document_type"];

            if(isset($data["deleted"]))
                $DocumentObject->Deleted = $data["deleted"];

            if(isset($data["owner_user_id"]))
                $DocumentObject->OwnerUserID = $data["owner_user_id"];

            if(isset($data["forward_user_id"]))
                $DocumentObject->ForwardUserID = $data["forward_user_id"];

            if(isset($data["access_type"]))
                $DocumentObject->AccessType = $data["access_type"];

            if(isset($data["access_roles"]))
                $DocumentObject->AccessRoles = AccessRoles::fromArray($data["access_roles"]);

            if(isset($data["flags"]))
                $DocumentObject->Flags = $data["flags"];

            if(isset($data["properties"]))
                $DocumentObject->Properties = Properties::fromArray($data["properties"]);

            if(isset($data["last_accessed_timestamp"]))
                $DocumentObject->LastAccessedTimestamp = $data["last_accessed_timestamp"];

            if(isset($data["created_timestamp"]))
                $DocumentObject->CreatedTimestamp = $data["created_timestamp"];

            return $DocumentObject;
        }
    }
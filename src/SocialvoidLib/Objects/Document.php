<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects;

    use SocialvoidLib\Abstracts\Types\Security\DocumentAccessType;
    use SocialvoidLib\Objects\Document\Properties;
    use SocialvoidLib\Objects\Document\ThirdPartySource;

    /**
     * Class Document
     * @package SocialvoidLib\Objects
     */
    class Document
    {
        /**
         * The Public ID of this record
         *
         * @var string
         */
        public $PublicID;

        /**
         * The source of the content
         *
         * @var string
         */
        public $ContentSource;

        /**
         * The
         *
         * @var string|null
         */
        public $CdnPublicID;

        /**
         * The third-party source if applicable to the document's source
         *
         * @var ThirdPartySource|null
         */
        public $ThirdPartySource;

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
        public $FileExtension;

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
         * The Unix Timestamp for when this record's properties (other than last accessed) was last updated
         *
         * @var int
         */
        public $LastUpdatedTimestamp;

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
                "public_id" => $this->PublicID,
                "content_source" => $this->ContentSource,
                "cdn_public_id" => $this->CdnPublicID,
                "third_party_source" =>  ($this->ThirdPartySource == null ? null : $this->ThirdPartySource->toArray()),
                "file_mime" => $this->FileMime,
                "file_size" => $this->FileSize,
                "file_name" => $this->FileName,
                "file_extension" => $this->FileExtension,
                "owner_user_id" => $this->OwnerUserID,
                "forward_user_id" => $this->ForwardUserID,
                "access_type" => $this->AccessType,
                "access_roles" => $this->AccessRoles->toArray(),
                "flags" => $this->Flags,
                "properties" => $this->Properties->toArray(),
                "last_updated_timestamp" => $this->LastUpdatedTimestamp,
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

            if(Isset($data["public_id"]))
                $DocumentObject->PublicID = $data["public_id"];

            if(isset($data["content_source"]))
                $DocumentObject->ContentSource = $data["content_source"];

            if(isset($data["cdn_public_id"]))
                $DocumentObject->CdnPublicID = $data["cdn_public_id"];

            if(isset($data["third_party_source"]))
                $DocumentObject->ThirdPartySource = ThirdPartySource::fromArray($data["third_party_source"]);

            if(isset($data["file_mime"]))
                $DocumentObject->FileMime = $data["file_mime"];

            if(isset($data["file_size"]))
                $DocumentObject->FileSize = $data["file_size"];

            if(isset($data["file_name"]))
                $DocumentObject->FileName = $data["file_name"];

            if(isset($data["file_extension"]))
                $DocumentObject->FileExtension = $data["file_extension"];

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

            if(isset($data["last_updated_timestamp"]))
                $DocumentObject->LastUpdatedTimestamp = $data["last_updated_timestamp"];

            if(isset($data["last_accessed_timestamp"]))
                $DocumentObject->LastAccessedTimestamp = $data["last_accessed_timestamp"];

            if(isset($data["created_timestamp"]))
                $DocumentObject->CreatedTimestamp = $data["created_timestamp"];

            return $DocumentObject;
        }
    }
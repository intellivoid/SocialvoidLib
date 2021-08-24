<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\InputTypes;

    use SocialvoidLib\Abstracts\Types\Security\DocumentAccessType;
    use SocialvoidLib\Objects\AccessRoles;
    use SocialvoidLib\Objects\Document\ThirdPartySource;

    /**
     * Class DocumentInput
     * @package SocialvoidLib\InputTypes
     */
    class DocumentInput
    {
        /**
         * Content source of where the document is coming from (and or who's hosting it)
         *
         * @var string
         */
        public $ContentSource;

        /**
         * The identifier for the source of the content
         *
         * @var string
         */
        public $ContentIdentifier;

        /**
         * The User ID that owns this document and registered it originally into the database
         *
         * @var int
         */
        public $OwnerUserID;

        /**
         * The access type in relation to the access roles for this document
         *
         * @var string|DocumentAccessType
         */
        public $AccessType;

        /**
         * The access roles for this document
         *
         * @var AccessRoles
         */
        public $AccessRoles;

        /**
         * The path to the file to be processed
         *
         * @var string
         */
        public $FilePath;

        /**
         * DocumentInput constructor.
         */
        public function __construct()
        {
            $this->AccessRoles = new AccessRoles();
            $this->AccessType = DocumentAccessType::Protected;
        }
    }
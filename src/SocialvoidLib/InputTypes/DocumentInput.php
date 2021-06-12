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
         * The CDN public ID if the content source is from the network
         *
         * @var string|null
         */
        public $CdnPublicID;

        /**
         * The Third Party source if the content source is from a third party network/service (eg; Twitter)
         *
         * NOTE: It is preferable that the network downloads and processes it's own copy than to depend on third-party
         * sources for the content as at anytime the content can be made unavailable.
         *
         * @var ThirdPartySource|null
         */
        public $ThirdPartySource;

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
         * DocumentInput constructor.
         */
        public function __construct()
        {
            $this->AccessRoles = new AccessRoles();
            $this->AccessType = DocumentAccessType::Protected;
        }
    }
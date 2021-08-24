<?php


    namespace SocialvoidLib\Managers;

    use msqg\QueryBuilder;
    use SocialvoidLib\Classes\Standard\BaseIdentification;
    use SocialvoidLib\InputTypes\DocumentInput;
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
         * Creates a new document record in the database from the document input
         *
         * @param DocumentInput $documentInput
         * @return Document
         */
        public function createDocument(DocumentInput $documentInput): Document
        {
            // TODO: Validate object
            // TODO: Analyze the file before processing
            // TODO: Finish this function
            $public_id = BaseIdentification::documentId($documentInput);

            $query = QueryBuilder::insert_into("documents", [
                "public_id" => $this->socialvoidLib->getDatabase()->real_escape_string($public_id),
                "content_source" => $this->socialvoidLib->getDatabase()->real_escape_string($documentInput->ContentSource),
                "cdn_public_id" => (
                    $documentInput->CdnPublicID == null ? null :
                    $this->socialvoidLib->getDatabase()->real_escape_string($documentInput->CdnPublicID)),
                "third_party_source" => (
                    $documentInput->ThirdPartySource == null ? null :
                    $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($documentInput->ThirdPartySource->toArray()))
                )
            ]);

            return new Document();
        }

    }
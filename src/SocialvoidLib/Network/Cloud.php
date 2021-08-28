<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Network;


    use Exception;
    use SocialvoidLib\Abstracts\ContentSource;
    use SocialvoidLib\Abstracts\Types\FetchLocationType;
    use SocialvoidLib\Abstracts\Types\MediaType;
    use SocialvoidLib\Classes\Security\ImageProcessing;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\Standard\Media\InvalidImageDimensionsException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\FileUploadException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\ContentResults;
    use SocialvoidLib\Objects\Post\MediaContent;

    /**
     * Class Cloud
     * @package SocialvoidLib\Network
     */
    class Cloud
    {
        /**
         * @var NetworkSession
         */
        private NetworkSession $networkSession;

        /**
         * Users constructor.
         * @param NetworkSession $networkSession
         */
        public function __construct(NetworkSession $networkSession)
        {
            $this->networkSession = $networkSession;
        }

        /**
         * Fetches a document from the cloud
         *
         * @param string $document_id
         * @return ContentResults
         * @throws DocumentNotFoundException
         * @throws DatabaseException
         */
        public function getDocument(string $document_id): ContentResults
        {
            // TODO: Check if user is authenticated
            // TODO: Run access role check
            $parsed_id = explode('-', $document_id);

            if(count($parsed_id) !== 2)
                throw new DocumentNotFoundException('The requested document was not found in the network');

            $document = $this->networkSession->getSocialvoidLib()->getDocumentsManager()->getDocument($parsed_id[0]);
            $file = $document->getFile($parsed_id[1]);

            if($file == null)
                throw new DocumentNotFoundException('The requested document was not found in the network');

            $content_results = new ContentResults();
            $content_results->ContentSource = $document->ContentSource;
            $content_results->ContentIdentifier = $document->ContentIdentifier;
            $content_results->DocumentID = $document->ID;
            $content_results->FileID = $file->ID;
            $content_results->FileMime = $file->Mime;
            $content_results->FileName = $file->Name;
            $content_results->FileSize = $file->Size;
            $content_results->FileHash = $parsed_id[1];

            switch($document->ContentSource)
            {
                case ContentSource::UserProfilePicture:
                    $content_results->FetchLocationType = FetchLocationType::Custom;
                    $content_results->Location = $this->networkSession->getSocialvoidLib()->getUserDisplayPictureManager()->getAvatarLocation($document->ContentIdentifier);
                    break;

                default:
                    $content_results->FetchLocationType = FetchLocationType::None;
                    break;
            }

            return $content_results;
        }
    }
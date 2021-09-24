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


    use Defuse\Crypto\Exception\BadFormatException;
    use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
    use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
    use Exception;
    use Longman\TelegramBot\Exception\TelegramException;
    use SocialvoidLib\Abstracts\ContentSource;
    use SocialvoidLib\Abstracts\Types\AccessEntityType;
    use SocialvoidLib\Abstracts\Types\FetchLocationType;
    use SocialvoidLib\Abstracts\Types\MediaType;
    use SocialvoidLib\Abstracts\Types\Security\DocumentAccessType;
    use SocialvoidLib\Classes\Security\ImageProcessing;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Internal\CdnFileNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Authentication\BadSessionChallengeAnswerException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionExpiredException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Media\InvalidImageDimensionsException;
    use SocialvoidLib\Exceptions\Standard\Network\AccessDeniedException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\FileUploadException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPublicHashException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\ContentResults;
    use SocialvoidLib\Objects\Post\MediaContent;
    use SocialvoidLib\Objects\Standard\SessionIdentification;
    use TelegramCDN\Exceptions\FileSecurityException;
    use udp2\Exceptions\AvatarNotFoundException;
    use Zimage\Exceptions\CannotGetOriginalImageException;
    use Zimage\Exceptions\FileNotFoundException;
    use Zimage\Exceptions\InvalidZimageFileException;
    use Zimage\Exceptions\SizeNotSetException;
    use Zimage\Exceptions\UnsupportedImageTypeException;
    use Zimage\Objects\Size;

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
         * @param SessionIdentification $sessionIdentification
         * @param string $document_id
         * @return ContentResults
         * @throws AccessDeniedException
         * @throws BadSessionChallengeAnswerException
         * @throws CacheException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         */
        public function getDocument(SessionIdentification $sessionIdentification, string $document_id): ContentResults
        {
            $this->networkSession->loadSession($sessionIdentification);
            if ($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $parsed_id = explode('-', $document_id);

            if (count($parsed_id) !== 3)
                throw new DocumentNotFoundException('The requested document was not found in the network (-1)');

            $document = $this->networkSession->getSocialvoidLib()->getDocumentsManager()->getDocument($parsed_id[0] . '-' . $parsed_id[1]);
            $file = $document->getFile($parsed_id[2]);

            if ($file == null)
                throw new DocumentNotFoundException('The requested document was not found in the network (-2)');

            $has_access = false;

            switch ($document->AccessType)
            {
                // TODO: Improve the access types
                case DocumentAccessType::Public:
                case DocumentAccessType::None:
                    $has_access = true;
                    break;

                case DocumentAccessType::Private:
                    if(
                        ($document->ForwardUserID !== null && $document->ForwardUserID == $this->networkSession->getAuthenticatedUser()->ID) ||
                        $document->OwnerUserID == $this->networkSession->getAuthenticatedUser()->ID
                    )
                    {
                        $has_access = true;
                    }
                    else
                    {
                        $has_access = $document->AccessRoles->hasAccess(AccessEntityType::Peer, $this->networkSession->getAuthenticatedUser()->ID);
                    }
            }

            if($has_access == false)
                throw new AccessDeniedException('Insufficient permissions to access the requested document');

            $content_results = new ContentResults();
            $content_results->ContentSource = $document->ContentSource;
            $content_results->ContentIdentifier = $document->ContentIdentifier;
            $content_results->CreatedTimestamp = $document->CreatedTimestamp;
            $content_results->Flags = $document->Flags;
            $content_results->DocumentID = $document->ID;
            $content_results->FileID = $file->ID;
            $content_results->FileMime = $file->Mime;
            $content_results->FileName = $file->Name;
            $content_results->FileSize = $file->Size;
            $content_results->FileType = $file->Type;
            $content_results->FileHash = $parsed_id[2];

            switch($document->ContentSource)
            {
                case ContentSource::UserProfilePicture:
                case ContentSource::TelegramCdn:
                    $content_results->FetchLocationType = FetchLocationType::Custom;
                    break;

                default:
                    $content_results->FetchLocationType = FetchLocationType::None;
                    break;
            }

            return $content_results;
        }

        /**
         * Returns the contents of a document
         *
         * @param ContentResults $contentResults
         * @return string|null
         * @throws CacheException
         * @throws DatabaseException
         * @throws BadFormatException
         * @throws EnvironmentIsBrokenException
         * @throws WrongKeyOrModifiedCiphertextException
         * @throws TelegramException
         * @throws CdnFileNotFoundException
         * @throws FileSecurityException
         * @throws CannotGetOriginalImageException
         * @throws FileNotFoundException
         * @throws InvalidZimageFileException
         * @throws SizeNotSetException
         * @throws UnsupportedImageTypeException
         * @throws AvatarNotFoundException
         */
        public function getDocumentContents(ContentResults $contentResults): ?string
        {
            switch($contentResults->FetchLocationType)
            {
                // A custom source requires code to be executed to obtain the resource
                case FetchLocationType::Custom:
                    switch($contentResults->ContentSource)
                    {
                        // A user profile picture
                        case ContentSource::UserProfilePicture:
                            $avatar = $this->networkSession->getSocialvoidLib()->getUserDisplayPictureManager()->getAvatar($contentResults->ContentIdentifier);
                            $image_data = $avatar->getImageBySize(new Size($contentResults->FileID));
                            return $image_data->getData();

                        case ContentSource::TelegramCdn:
                            $cdn_content_record = $this->networkSession->getSocialvoidLib()->getTelegramCdnManager()->getUploadRecord($contentResults->ContentIdentifier);
                            return $this->networkSession->getSocialvoidLib()->getTelegramCdnManager()->downloadFile($cdn_content_record);
                    }

            }

            return null;
        }
    }
<?php

    namespace SocialvoidLib\Network;

    use Defuse\Crypto\Exception\BadFormatException;
    use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
    use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
    use Exception;
    use Longman\TelegramBot\Exception\TelegramException;
    use SocialvoidLib\Abstracts\Types\Standard\DocumentType;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\GenericInternal\CacheException;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Internal\CdnFileNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Authentication\BadSessionChallengeAnswerException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionExpiredException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Network\PeerNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\Exceptions\Standard\Validation\FileTooLargeException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPublicHashException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidFileForProfilePictureException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidFirstNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidLastNameException;
    use SocialvoidLib\NetworkSession;
    use SocialvoidLib\Objects\Standard\SessionIdentification;
    use TelegramCDN\Exceptions\FileSecurityException;
    use TmpFile\TmpFile;
    use udp2\Exceptions\AvatarNotFoundException;
    use Zimage\Exceptions\CannotGetOriginalImageException;
    use Zimage\Exceptions\FileNotFoundException;
    use Zimage\Exceptions\InvalidZimageFileException;
    use Zimage\Exceptions\SizeNotSetException;
    use Zimage\Exceptions\UnsupportedImageTypeException;

    class Account
    {
        /**
         * @var NetworkSession
         */
        private NetworkSession $networkSession;

        /**
         * Account constructor.
         * @param NetworkSession $networkSession
         */
        public function __construct(NetworkSession $networkSession)
        {
            $this->networkSession = $networkSession;
        }

        /**
         * @param SessionIdentification $sessionIdentification
         * @param string $document_id
         * @return bool
         * @throws AvatarNotFoundException
         * @throws BadSessionChallengeAnswerException
         * @throws CacheException
         * @throws CannotGetOriginalImageException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws FileNotFoundException
         * @throws FileTooLargeException
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidFileForProfilePictureException
         * @throws InvalidSearchMethodException
         * @throws InvalidZimageFileException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         * @throws SizeNotSetException
         * @throws UnsupportedImageTypeException
         * @throws BadFormatException
         * @throws EnvironmentIsBrokenException
         * @throws WrongKeyOrModifiedCiphertextException
         * @throws TelegramException
         * @throws CdnFileNotFoundException
         * @throws FileSecurityException
         */
        public function setProfilePicture(SessionIdentification $sessionIdentification, string $document_id): bool
        {
            $this->networkSession->loadSession($sessionIdentification);
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $requested_document = $this->networkSession->getCloud()->getDocument($sessionIdentification, $document_id);
            if($requested_document->FileType !== DocumentType::Photo)
                throw new InvalidFileForProfilePictureException('The file type must be a photo, got ' . strtolower($requested_document->FileType));

            if($requested_document->FileSize > 8388608) // 8MB
                throw new FileTooLargeException('The file size cannot be larger than 8MB');

            $TmpFile = new TmpFile($this->networkSession->getCloud()->getDocumentContents($requested_document));
            $image_size = @getimagesize($TmpFile->getFileName());
            if($image_size == false)
                throw new InvalidFileForProfilePictureException('The given file is not a supported image file for a profile picture');

            // Detect the image type and convert it accordingly if necessary
            switch($image_size[2])
            {
                case IMAGETYPE_PNG:
                    if((imagetypes() & IMAGETYPE_PNG) == false)
                        throw new InvalidFileForProfilePictureException('PNG File types are not supported on this system');

                    // Use dedicated function to convert PNG files to JPEG
                    Utilities::convertPngToJpeg($TmpFile->getFileName());
                    break;

                case IMAGETYPE_JPEG:
                    if((imagetypes() & IMAGETYPE_JPEG) == false)
                        throw new InvalidFileForProfilePictureException('JPEG File types are not supported on this system');
                    break;

                case IMAGETYPE_WEBP:
                    if((imagetypes() & IMAGETYPE_WEBP) == false)
                        throw new InvalidFileForProfilePictureException('WEBP File types are not supported on this system');
                    break;

                default:
                    throw new InvalidFileForProfilePictureException('The given file is not a supported image file for a profile picture');
            }

            // Resize the image to an appropriate resolution size while preserving the aspect ratio
            Utilities::resizeImage($TmpFile->getFileName(), 640, 640);
            $user = $this->networkSession->getAuthenticatedUser();

            try
            {
                $this->networkSession->getSocialvoidLib()->getUserManager()->setDisplayPicture(
                    $user, $TmpFile->getFileName()
                );
            }
            catch(Exception $e)
            {
                throw new InternalServerException('There was an error while trying to update the user profile picture', $e);
            }
            $this->networkSession->setAuthenticatedUser($user);

            return true;
        }

        /**
         * Deletes the current profile picture set by the user
         *
         * @param SessionIdentification $sessionIdentification
         * @return bool
         * @throws BadSessionChallengeAnswerException
         * @throws CacheException
         * @throws DatabaseException
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         */
        public function deleteProfilePicture(SessionIdentification $sessionIdentification): bool
        {
            $this->networkSession->loadSession($sessionIdentification);
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $user = $this->networkSession->getAuthenticatedUser();

            try
            {
                $this->networkSession->getSocialvoidLib()->getUserManager()->deleteDisplayPicture($user);
            }
            catch(Exception $e)
            {
                throw new InternalServerException('There was an error while trying to update the user profile picture', $e);
            }

            $this->networkSession->setAuthenticatedUser($user);

            return true;
        }

        /**
         * Changes the display name of the peer
         *
         * @param SessionIdentification $sessionIdentification
         * @param string $first_name
         * @param string|null $last_name
         * @return bool
         * @throws BadSessionChallengeAnswerException
         * @throws CacheException
         * @throws DatabaseException
         * @throws InternalServerException
         * @throws InvalidClientPublicHashException
         * @throws InvalidFirstNameException
         * @throws InvalidLastNameException
         * @throws InvalidSearchMethodException
         * @throws NotAuthenticatedException
         * @throws PeerNotFoundException
         * @throws SessionExpiredException
         * @throws SessionNotFoundException
         */
        public function updateName(SessionIdentification $sessionIdentification, string $first_name, ?string $last_name=null): bool
        {
            $this->networkSession->loadSession($sessionIdentification);
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            if(Validate::firstName($first_name) == false)
                throw new InvalidFirstNameException('The given first name is invalid', $first_name);

            if($last_name !== null && Validate::lastName($last_name) == false)
                throw new InvalidLastNameException('The given last name is invalid', $last_name);

            $user = $this->networkSession->getAuthenticatedUser();
            $user->Profile->FirstName = $first_name;
            $user->Profile->LastName = $last_name;

            try
            {
                $this->networkSession->getSocialvoidLib()->getUserManager()->updateUser($user);
            }
            catch(Exception $e)
            {
                throw new InternalServerException('There was an error while trying to update the user profile picture', $e);
            }

            $this->networkSession->setAuthenticatedUser($user);

            return true;
        }
    }
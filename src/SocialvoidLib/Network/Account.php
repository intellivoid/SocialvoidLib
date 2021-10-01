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
    use SocialvoidLib\Exceptions\Internal\CdnFileNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException;
    use SocialvoidLib\Exceptions\Standard\Network\AccessDeniedException;
    use SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Server\InternalServerException;
    use SocialvoidLib\Exceptions\Standard\Validation\FileTooLargeException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidBiographyException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidFileForProfilePictureException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidFirstNameException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidLastNameException;
    use SocialvoidLib\NetworkSession;
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
         * @param string $document_id
         * @return bool
         * @throws AvatarNotFoundException
         * @throws BadFormatException
         * @throws CacheException
         * @throws CannotGetOriginalImageException
         * @throws CdnFileNotFoundException
         * @throws DatabaseException
         * @throws DocumentNotFoundException
         * @throws EnvironmentIsBrokenException
         * @throws FileNotFoundException
         * @throws FileSecurityException
         * @throws FileTooLargeException
         * @throws InternalServerException
         * @throws InvalidFileForProfilePictureException
         * @throws InvalidZimageFileException
         * @throws NotAuthenticatedException
         * @throws SizeNotSetException
         * @throws TelegramException
         * @throws UnsupportedImageTypeException
         * @throws WrongKeyOrModifiedCiphertextException
         * @throws AccessDeniedException
         */
        public function setProfilePicture(string $document_id): bool
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $requested_document = $this->networkSession->getCloud()->getDocument($document_id);
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
         * @return bool
         * @throws InternalServerException
         * @throws NotAuthenticatedException
         */
        public function deleteProfilePicture(): bool
        {
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
         * @param string $first_name
         * @param string|null $last_name
         * @return bool
         * @throws InternalServerException
         * @throws InvalidFirstNameException
         * @throws InvalidLastNameException
         * @throws NotAuthenticatedException
         */
        public function updateName(string $first_name, ?string $last_name=null): bool
        {
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
                throw new InternalServerException('There was an error while trying to update the display name', $e);
            }

            $this->networkSession->setAuthenticatedUser($user);

            return true;
        }

        /**
         * Updates the user's biography
         *
         * @param string $biography
         * @return bool
         * @throws InternalServerException
         * @throws InvalidBiographyException
         * @throws NotAuthenticatedException
         */
        public function updateBiography(string $biography): bool
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            if(Validate::biography($biography) == false)
                throw new InvalidBiographyException('The given biography is invalid', $biography);

            $user = $this->networkSession->getAuthenticatedUser();
            $user->Profile->Biography = $biography;

            try
            {
                $this->networkSession->getSocialvoidLib()->getUserManager()->updateUser($user);
            }
            catch(Exception $e)
            {
                throw new InternalServerException('There was an error while trying to update the biography', $e);
            }

            $this->networkSession->setAuthenticatedUser($user);

            return true;
        }

        /**
         * Clears the user biography
         *
         * @return bool
         * @throws InternalServerException
         * @throws NotAuthenticatedException
         */
        public function clearBiography(): bool
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $user = $this->networkSession->getAuthenticatedUser();
            $user->Profile->Biography = null;

            try
            {
                $this->networkSession->getSocialvoidLib()->getUserManager()->updateUser($user);
            }
            catch(Exception $e)
            {
                throw new InternalServerException('There was an error while trying to update the biography', $e);
            }

            $this->networkSession->setAuthenticatedUser($user);

            return true;
        }

        /**
         * Updates the user's location
         *
         * @param string $location
         * @return bool
         * @throws InternalServerException
         * @throws NotAuthenticatedException
         */
        public function updateLocation(string $location): bool
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            // TODO: Validate location

            $user = $this->networkSession->getAuthenticatedUser();
            $user->Profile->Location = $location;

            try
            {
                $this->networkSession->getSocialvoidLib()->getUserManager()->updateUser($user);
            }
            catch(Exception $e)
            {
                throw new InternalServerException('There was an error while trying to update the location', $e);
            }

            $this->networkSession->setAuthenticatedUser($user);

            return true;
        }

        /**
         * Clears the user location
         *
         * @return bool
         * @throws InternalServerException
         * @throws NotAuthenticatedException
         */
        public function clearLocation(): bool
        {
            if($this->networkSession->isAuthenticated() == false)
                throw new NotAuthenticatedException();

            $user = $this->networkSession->getAuthenticatedUser();
            $user->Profile->Location = null;

            try
            {
                $this->networkSession->getSocialvoidLib()->getUserManager()->updateUser($user);
            }
            catch(Exception $e)
            {
                throw new InternalServerException('There was an error while trying to update the location', $e);
            }

            $this->networkSession->setAuthenticatedUser($user);

            return true;
        }
    }
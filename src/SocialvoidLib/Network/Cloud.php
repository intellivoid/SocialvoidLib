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
    use SocialvoidLib\Abstracts\Types\MediaType;
    use SocialvoidLib\Classes\Security\ImageProcessing;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Exceptions\Standard\Media\InvalidImageDimensionsException;
    use SocialvoidLib\Exceptions\Standard\Network\FileUploadException;
    use SocialvoidLib\NetworkSession;
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
         * Uploads an image to the CDN and returns a media content object
         *
         * @param string $file_path
         * @return MediaContent
         * @throws FileUploadException
         */
        public function uploadImage(string $file_path): MediaContent
        {
            try
            {
                $image_details = ImageProcessing::verifyImage($file_path);
            }
            catch(Exception $e)
            {
                throw new FileUploadException("The given image file is invalid and cannot be processed", $e);
            }

            if($image_details->Width > 10000 || $image_details->Height > 10000)
            {
                throw new FileUploadException("The given image file exceeds in the supported dimensions, 10000.",
                    new InvalidImageDimensionsException("The given image file exceeds in the supported dimensions, 10000")
                );
            }

            try {
                $uploaded_content = $this->networkSession->getSocialvoidLib()->getTelegramCdnManager()->uploadContent(
                    $file_path, $this->networkSession->getAuthenticatedUser()->PublicID
                );
            }
            catch(Exception $e)
            {
                throw new FileUploadException("There was an error while trying to upload the file to the CDN", $e);
            }

            $media_content = new MediaContent();
            $media_content->ProviderUrl = "https://" . Utilities::getBoolDefinition("SOCIALVOID_LIB_NETWORK_DOMAIN");
            $media_content->ProviderName = Utilities::getBoolDefinition("SOCIALVOID_LIB_NETWORK_NAME");
            $media_content->MediaType = MediaType::Image;
            $media_content->URL = 
                "https://cdn." . Utilities::getBoolDefinition("SOCIALVOID_LIB_NETWORK_DOMAIN") . 
                "/public/" . $uploaded_content;
            
            return $media_content;
        }

        public function getDocument(string $document_id): MediaContent
        {

        }
    }
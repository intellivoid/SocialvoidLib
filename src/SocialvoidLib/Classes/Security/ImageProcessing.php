<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Classes\Security;


    use Exception;
    use SocialvoidLib\Abstracts\Types\ImageType;
    use SocialvoidLib\Exceptions\GenericInternal\FileNotFoundException;
    use SocialvoidLib\Exceptions\Internal\FileTooLargeException;
    use SocialvoidLib\Exceptions\Internal\InvalidImageTypeException;
    use SocialvoidLib\Objects\ImageDetails;

    /**
     * Class ImageProcessing
     * @package SocialvoidLib\Classes\Security
     */
    class ImageProcessing
    {
        /**
         * Verifies if the file is an actual image
         *
         * @param string $file_path
         * @return ImageDetails
         * @throws FileNotFoundException
         * @throws FileTooLargeException
         * @throws InvalidImageTypeException
         */
        public static function verifyImage(string $file_path): ImageDetails
        {
            if(file_exists($file_path) == false)
                throw new FileNotFoundException("The file '$file_path' was not found.", $file_path);

            if(strlen(file_get_contents($file_path)) > 15728640)
                throw new FileTooLargeException("The file '$file_path' is too large to process");

            try
            {
                $ImageSize = getimagesize($file_path);
            }
            catch(Exception $exception)
            {
                throw new InvalidImageTypeException("Corrupted image file");
            }

            if(!$ImageSize)
            {
                throw new InvalidImageTypeException("Corrupted image file");
            }

            $valid_types = array(IMAGETYPE_JPEG, IMAGETYPE_PNG);

            if(in_array($ImageSize[2],  $valid_types) == false)
            {
                throw new InvalidImageTypeException("The given file type is unsupported");
            }

            $ImageDetailsObject = new ImageDetails();
            $ImageDetailsObject->Width = $ImageSize[0];
            $ImageDetailsObject->Height = $ImageSize[1];

            if($ImageSize[2] == IMAGETYPE_JPEG)
            {
                $ImageDetailsObject->ImageType = ImageType::JPEG;
            }

            if($ImageSize[2] == IMAGETYPE_PNG)
            {
                $ImageDetailsObject->ImageType = ImageType::PNG;
            }

            return $ImageDetailsObject;
        }
    }
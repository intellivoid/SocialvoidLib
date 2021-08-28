<?php

    require('ppm');

    import("net.intellivoid.socialvoidlib");

    $socialvoidlib = new \SocialvoidLib\SocialvoidLib();
    $networkSession = new SocialvoidLib\NetworkSession($socialvoidlib);

    if(isset($_GET['document_id']))
    {
        try
        {
            $content_results = $networkSession->getCloud()->getDocument($_GET['document_id']);

            switch($content_results->FetchLocationType)
            {
                // A custom source requires code to be executed to obtain the resource
                case \SocialvoidLib\Abstracts\Types\FetchLocationType::Custom:
                    switch($content_results->ContentSource)
                    {
                        // A user profile picture
                        case \SocialvoidLib\Abstracts\ContentSource::UserProfilePicture:
                            $avatar = $socialvoidlib->getUserDisplayPictureManager()->getAvatar($content_results->ContentIdentifier);
                            $image_data = $avatar->getImageBySize(new \Zimage\Objects\Size($content_results->FileID));
                            \SocialvoidLib\Classes\Utilities::setContentHeaders($content_results);
                            print($image_data->getData());
                            break;
                    }

            }
        }
        catch (\SocialvoidLib\Exceptions\GenericInternal\DatabaseException $e)
        {
            http_response_code(500);
            print("<h1>500 Internal Database Error</h1>" . PHP_EOL);
            print("<code>");
            var_dump($e);
            print("</code>");
        }
        catch (\SocialvoidLib\Exceptions\Standard\Network\DocumentNotFoundException $e)
        {
            http_response_code(404);
            print("<h1>404 Document not found.</h1>");
        }
    }
<?php

    /**
     * A simple CDN Handler for Socialvoid
     */

    require('ppm');
    import("net.intellivoid.socialvoidlib");

    /**
     * Returns an HTTP parameter
     *
     * @param string $name
     * @return string|null
     */
    function getParameter(string $name): ?string
    {
        if(isset($_GET[$name]))
            return $_GET[$name];

        if(isset($_POST[$name]))
            return $_POST[$name];

        return null;
    }

    /**
     * Returns an error response in JSON
     *
     * @param Exception $e
     */
    function returnErrorResponse(Exception $e)
    {
        $response = [
            'success' => false,
            'response_code' => 500,
            'error_code' => \SocialvoidLib\Abstracts\StandardErrorCodes::InternalServerError,
            'message' => 'There was an unexpected error while trying to handle your request'
        ];

        if(\SocialvoidLib\Classes\Validate::isStandardError($e->getCode()))
        {
            $response['error_code'] = $e->getCode();
            $response['message'] = $e->getMessage();

            switch($e->getCode())
            {
                case \SocialvoidLib\Abstracts\StandardErrorCodes::BadSessionChallengeAnswerException:
                case \SocialvoidLib\Abstracts\StandardErrorCodes::InvalidClientPublicHashException:
                case \SocialvoidLib\Abstracts\StandardErrorCodes::InvalidSessionIdentificationException:
                case \SocialvoidLib\Abstracts\StandardErrorCodes::SessionExpiredException:
                case \SocialvoidLib\Abstracts\StandardErrorCodes::SessionNotFoundException:
                    $response['response_code'] = 400;
                    break;

                case \SocialvoidLib\Abstracts\StandardErrorCodes::NotAuthenticatedException:
                    $response['response_code'] = 401;
                    break;

                case \SocialvoidLib\Abstracts\StandardErrorCodes::DocumentNotFoundException:
                    $response['response_code'] = 404;
                    break;

                case \SocialvoidLib\Abstracts\StandardErrorCodes::AccessDeniedException:
                    $response['response_code'] = 403;
                    break;
            }
        }

        http_response_code($response['response_code']);
        header('Content-Type: application/json');
        unset($response['response_code']);
        print(json_encode($response, JSON_UNESCAPED_SLASHES));

        exit();
    }

    /**
     * Returns a missing parameter response
     *
     * @param string $parameter_name
     */
    function returnMissingParameterResponse(string $parameter_name)
    {
        $response = [
            'success' => false,
            'response_code' => 400,
            'error_code' => 0,
            'message' => 'Missing parameter \'' . $parameter_name . '\''
        ];

        http_response_code($response['response_code']);
        header('Content-Type: application/json');
        unset($response['response_code']);
        print(json_encode($response, JSON_UNESCAPED_SLASHES));
        exit();
    }

    if(getParameter('document') == null)
        returnMissingParameterResponse('document');
    if(getParameter('session_id') == null)
        returnMissingParameterResponse('session_id');
    if(getParameter('client_public_hash') == null)
        returnMissingParameterResponse('client_public_hash');
    if(getParameter('challenge_answer') == null)
        returnMissingParameterResponse('challenge_answer');

    try
    {
        $SessionIdentification = \SocialvoidLib\Objects\Standard\SessionIdentification::fromArray([
            'session_id' => getParameter('session_id'),
            'client_public_hash' => getParameter('client_public_hash'),
            'challenge_answer' => getParameter('challenge_answer'),
        ]);
        $SessionIdentification->validate();

        $socialvoidlib = new \SocialvoidLib\SocialvoidLib();
        $networkSession = new SocialvoidLib\NetworkSession($socialvoidlib);
        $networkSession->loadSession($SessionIdentification);
    }
    catch(Exception $e)
    {
        returnErrorResponse($e);
        exit();
    }

    try
    {
        $content_results = $networkSession->getCloud()->getDocument($SessionIdentification, getParameter('document'));

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
    catch (Exception $e)
    {
        returnErrorResponse($e);
    }
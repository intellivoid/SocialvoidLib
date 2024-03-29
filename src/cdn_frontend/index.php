<?php

    /** @noinspection PhpFullyQualifiedNameUsageInspection */


    ini_set('display_errors', 'Off');

    /**
     * A simple CDN Handler for Socialvoid
     */

    use HttpStream\Exceptions\RequestRangeNotSatisfiableException;
    use HttpStream\HttpStream;
    use SocialvoidLib\Abstracts\ContentSource;
    use SocialvoidLib\Abstracts\Types\Security\DocumentAccessType;
    use SocialvoidLib\InputTypes\DocumentInput;

    require('ppm');
    /** @noinspection PhpUnhandledExceptionInspection */
    import("net.intellivoid.socialvoidlib");

    /**
     * @return array
     */
    function parseRawHttpBoy(): array
    {
        // read incoming data
        $input = file_get_contents('php://input');


        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        $boundary = $matches[1];

        // split content by boundary and get rid of last -- element
        $a_blocks = preg_split("/-+$boundary/", $input);
        array_pop($a_blocks);
        $a_data = [];

        // loop data blocks
        foreach ($a_blocks as $id => $block)
        {
            if (empty($block))
                continue;

            // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char

            // parse uploaded files
            if (strpos($block, 'application/octet-stream') !== FALSE)
            {
                // match "name", then everything after "stream" (optional) except for prepending newlines
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
            }
            // parse all other fields
            else
            {
                // match "name" and optional value in between newline sequences
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
            }
            $a_data[$matches[1]] = $matches[2];
        }

        return $a_data;
    }

    /**
     * Returns an HTTP parameter
     *
     * @param string $name
     * @return string|null
     */
    function getParameter(string $name)
    {
        if(isset($_GET[$name]))
            return $_GET[$name];

        if(isset($_POST[$name]))
            return $_POST[$name];

        $entityBody = file_get_contents('php://input');
        if($entityBody !== false || $entityBody !== null)
        {
            $entityBodyDecoded = json_decode($entityBody, true);
            if($entityBodyDecoded !== false && isset($entityBodyDecoded[$name]))
            {
                switch(gettype(isset($entityBodyDecoded[$name])))
                {
                    case 'string':
                    case 'integer':
                    case 'boolean':
                        return $entityBodyDecoded[$name];
                }
            }
        }

        $parsedRaw = parseRawHttpBoy();
        if(isset($parsedRaw[$name]))
            return $parsedRaw[$name];
        return null;
    }

    /**
     * Sets the required headers to the http response
     */
    function setRequiredHeaders()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST,GET,OPTIONS');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
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
        setRequiredHeaders();
        unset($response['response_code']);
        if($_SERVER['REQUEST_METHOD'] !== 'HEAD')
        {
            print(json_encode($response, JSON_UNESCAPED_SLASHES));
        }
        exit();
    }

    /**
     * Returns a successful response
     *
     * @param array $results
     */
    function returnSuccessResponse(array $results)
    {
        $response = [
            'success' => true,
            'results' => $results,
        ];
        http_response_code(200);
        header('Content-Type: application/json');
        setRequiredHeaders();
        if($_SERVER['REQUEST_METHOD'] !== 'HEAD')
        {
            print(json_encode($response, JSON_UNESCAPED_SLASHES));
        }
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
        setRequiredHeaders();
        unset($response['response_code']);
        if($_SERVER['REQUEST_METHOD'] !== 'HEAD')
        {
            print(json_encode($response, JSON_UNESCAPED_SLASHES));
        }
        exit();
    }

    /**
     * Returns a bad action response
     */
    function returnBadActionResponse()
    {
        $response = [
            'success' => false,
            'response_code' => 400,
            'error_code' => 0,
            'message' => 'The request action must be \'download\' or \'upload\''
        ];

        http_response_code($response['response_code']);
        header('Content-Type: application/json');
        setRequiredHeaders();
        unset($response['response_code']);
        print(json_encode($response, JSON_UNESCAPED_SLASHES));
        exit();
    }

    // Check the parameters!
    if(getParameter('action') == null)
        returnMissingParameterResponse('action');
    if(getParameter('session_id') == null)
        returnMissingParameterResponse('session_id');
    if(getParameter('client_public_hash') == null)
        returnMissingParameterResponse('client_public_hash');
    if(getParameter('challenge_answer') == null)
        returnMissingParameterResponse('challenge_answer');

    // Validate the session identification
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

        if($networkSession->isAuthenticated() == false)
            throw new \SocialvoidLib\Exceptions\Standard\Authentication\NotAuthenticatedException();

        $contentLength = true;
    }
    catch(Exception $e)
    {
        returnErrorResponse($e);
        exit();
    }

    // Download the document
    if(strtolower(getParameter('action')) == 'download')
    {
        if(getParameter('document') == null)
            returnMissingParameterResponse('document');

        try
        {
            $content_results = $networkSession->getCloud()->getDocument(getParameter('document'));
            $contentSourceLocation = $networkSession->getCloud()->getDocumentLocation($content_results);

            if($contentSourceLocation == null)
            {
                \SocialvoidLib\Classes\Utilities::setContentHeaders($content_results, $contentLength, true);
                if($_SERVER['REQUEST_METHOD'] !== 'HEAD')
                {
                    print($networkSession->getCloud()->getDocumentContents($content_results));
                }
                return;
            }

            switch($content_results->ContentSource)
            {
                case ContentSource::TelegramCdn:
                    $cdn_content_record = $networkSession->getSocialvoidLib()->getTelegramCdnManager()->getUploadRecord($content_results->ContentIdentifier);
                    $content_location = $networkSession->getSocialvoidLib()->getTelegramCdnManager()->getDownloadLocation($cdn_content_record);
                    $HttpStream = new HttpStream($content_location, true, false,
                        \Defuse\Crypto\Key::loadFromAsciiSafeString($cdn_content_record->EncryptionKey)
                    );

                    $headers = $HttpStream->getHttpResponse(true);
                    $real_headers = \SocialvoidLib\Classes\Utilities::getContentHeaders($content_results);

                    foreach($real_headers as $header_name => $header_value)
                    {
                        $headers->ResponseHeaders[$header_name] = $header_value;
                    }

                    try
                    {
                        $HttpStream->prepareStream();
                    }
                    catch (RequestRangeNotSatisfiableException $e)
                    {
                        http_response_code($headers->ResponseCode);
                        foreach ($headers->ResponseHeaders as $header => $header_value)
                        {
                            header("$header: $header_value");
                        }
                        setRequiredHeaders();
                        unlink($content_location);
                        return;
                    }

                    http_response_code($headers->ResponseCode);
                    foreach ($headers->ResponseHeaders as $header => $header_value)
                    {
                        header("$header: $header_value");
                    }
                    setRequiredHeaders();

                    if($_SERVER['REQUEST_METHOD'] !== 'HEAD')
                    {
                        ob_implicit_flush(true);
                        ob_end_flush();
                        $HttpStream->start_stream();
                    }

                    unlink($content_location);
                    break;

                default:
                    \SocialvoidLib\Classes\Utilities::setContentHeaders($content_results, $contentLength, true);
                    if($_SERVER['REQUEST_METHOD'] !== 'HEAD')
                    {
                        HttpStream::streamToHttp($contentSourceLocation, true);
                    }
                    else
                    {
                        $HttpStream = new HttpStream($contentSourceLocation, false);
                        $headers = $HttpStream->getHttpResponse(true);
                        http_response_code($headers->ResponseCode);
                        foreach ($headers as $header => $header_value)
                        {
                            header("$header: $header_value");
                        }
                        setRequiredHeaders();
                        return;
                    }
            }

        }
        catch (Exception $e)
        {
            http_response_code(500);
            setRequiredHeaders();
            returnErrorResponse($e);
        }
    }
    // Upload a document
    elseif(strtolower(getParameter('action')) == 'upload')
    {
        // Verify the upload
        if (!isset($_FILES['document']['error']) || is_array($_FILES['document']['error']))
        {
            returnErrorResponse(new \SocialvoidLib\Exceptions\Standard\Server\DocumentUploadException('Missing \'document\' field for file upload'));
        }

        // Verify the error
        switch ($_FILES['document']['error'])
        {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                returnErrorResponse(new \SocialvoidLib\Exceptions\Standard\Server\DocumentUploadException('\'document\' field contains no file'));
                break;

            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                returnErrorResponse(new \SocialvoidLib\Exceptions\Standard\Server\DocumentUploadException('File size limit exceeded (server)'));
                break;

            default:
                returnErrorResponse(new \SocialvoidLib\Exceptions\Standard\Server\DocumentUploadException('Unknown file upload error'));
                break;
        }

        // Verify the configured size limit
        if ($_FILES['document']['size'] > (int)$socialvoidlib->getCdnConfiguration()['MaxFileUploadSize'])
            returnErrorResponse(new \SocialvoidLib\Exceptions\Standard\Server\DocumentUploadException('File size limit exceeded'));

        // Verify the file name
        if(\SocialvoidLib\Classes\Validate::fileName($_FILES['document']['name']) == false)
            returnErrorResponse(new \SocialvoidLib\Exceptions\Standard\Validation\InvalidFileNameException('The given file name is invalid'));

        // Move uploaded file
        $TemporaryFile = new \TmpFile\TmpFile(null);
        if(!move_uploaded_file($_FILES['document']['tmp_name'], $TemporaryFile->getFileName()))
            returnErrorResponse(new \SocialvoidLib\Exceptions\Standard\Server\InternalServerException('There was an error while trying to process the file'));

        // Finally, process the file
        try
        {
            $file_id = $socialvoidlib->getTelegramCdnManager()->uploadContent($TemporaryFile->getFileName());
        }
        catch (Exception $e)
        {
            returnErrorResponse($e);
            exit();
        }

        $document_input = new DocumentInput();
        $document_input->AccessType = DocumentAccessType::None;
        $document_input->OwnerUserID = $networkSession->getAuthenticatedUser()->ID;
        $document_input->ContentSource = ContentSource::TelegramCdn;
        $document_input->ContentIdentifier = $file_id;

        try
        {
            $file_object = \SocialvoidLib\Objects\Document\File::fromFile($TemporaryFile->getFileName());
        }
        catch (Exception $e)
        {
            returnErrorResponse($e);
            exit();
        }

        $file_object->Name = $_FILES['document']['name'];
        $document_input->Files = [$file_object];

        try
        {
            $document_id = $socialvoidlib->getDocumentsManager()->createDocument($document_input);
            $document = $socialvoidlib->getDocumentsManager()->getDocument($document_id);
            returnSuccessResponse(\SocialvoidLib\Objects\Standard\Document::fromDocument($document, $file_object->Hash)->toArray());
        }
        catch (Exception $e)
        {
            returnErrorResponse($e);
            exit();
        }
    }
    else
    {
        returnBadActionResponse();
    }
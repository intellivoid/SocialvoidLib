<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    class ServerInformation
    {
        /**
         * The name of the network
         *
         * @var string
         */
        public $NetworkName;

        /**
         * The protocol version that the server is supporting
         *
         * @var string
         */
        public $ProtocolVersion = '1.0';

        /**
         * The endpoint of the CDN server that allows clients to upload and download documents to
         *
         * @var string
         */
        public $CdnServer;

        /**
         * The maximum size supported for file uploads in bytes
         *
         * @var int
         */
        public $UploadMaxFileSize;

        /**
         * The time-to-live for unauthorized sessions
         *
         * @var int
         */
        public $UnauthorizedSessionTTL;

        /**
         * The time-to-live for authorized sessions
         *
         * @var int
         */
        public $AuthorizedSessionTTL;

        /**
         * The limit to the amount of likes you can retrieve from a post
         *
         * @var int
         */
        public $RetrieveLikesMaxLimit;

        /**
         * The limit to the amount of reposts you can retrieve from a post
         *
         * @var int
         */
        public $RetrieveRepostsMaxLimit;

        /**
         * The limit to the amount of likes you can retrieve from a post
         *
         * @var int
         */
        public $RetrieveRepliesMaxLimit;

        /**
         * The limit to the amount of quotes you can retrieve from a post
         *
         * @var int
         */
        public $RetrieveQuotesMaxLimit;

        /**
         * Return an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'network_name' => $this->NetworkName,
                'protocol_version' => $this->ProtocolVersion,
                'cdn_server' => $this->CdnServer,
                'upload_max_file_size' => $this->UploadMaxFileSize,
                'unauthorized_session_ttl' => $this->UnauthorizedSessionTTL,
                'authorized_session_ttl' => $this->AuthorizedSessionTTL,
                'retrieve_likes_max_limit' => $this->RetrieveLikesMaxLimit,
                'retrieve_reposts_max_limit' => $this->RetrieveRepostsMaxLimit,
                'retrieve_replies_max_limit' => $this->RetrieveRepliesMaxLimit,
                'retrieve_quotes_max_limit' => $this->RetrieveQuotesMaxLimit
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return ServerInformation
         */
        public static function fromArray(array $data): ServerInformation
        {
            $serverInformationObject = new ServerInformation();

            if(isset($data['network_name']))
                $serverInformationObject->NetworkName = $data['network_name'];

            if(isset($data['protocol_version']))
                $serverInformationObject->ProtocolVersion = $data['protocol_version'];

            if(isset($data['cdn_server']))
                $serverInformationObject->CdnServer = $data['cdn_server'];

            if(isset($data['upload_max_file_size']))
                $serverInformationObject->UploadMaxFileSize = $data['upload_max_file_size'];

            if(isset($data['unauthorized_session_ttl']))
                $serverInformationObject->UnauthorizedSessionTTL = $data['unauthorized_session_ttl'];

            if(isset($data['authorized_session_ttl']))
                $serverInformationObject->AuthorizedSessionTTL = $data['authorized_session_ttl'];

            if(isset($data['retrieve_likes_max_limit']))
                $serverInformationObject->RetrieveLikesMaxLimit = $data['retrieve_likes_max_limit'];

            if(isset($data['retrieve_reposts_max_limit']))
                $serverInformationObject->RetrieveRepostsMaxLimit = $data['retrieve_reposts_max_limit'];

            if(isset($data['retrieve_replies_max_limit']))
                $serverInformationObject->RetrieveRepliesMaxLimit = $data['retrieve_replies_max_limit'];

            if(isset($data['retrieve_quotes_max_limit']))
                $serverInformationObject->RetrieveQuotesMaxLimit = $data['retrieve_quotes_max_limit'];

            return $serverInformationObject;
        }

    }
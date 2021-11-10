<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Types\BuiltinTypes;
    use SocialvoidLib\Interfaces\StandardObjectInterface;
    use SocialvoidLib\Objects\Standard\ObjectDefinition;
    use SocialvoidLib\Objects\Standard\ParameterDefinition;
    use SocialvoidLib\Objects\Standard\TypeDefinition;

    class ServerInformation implements StandardObjectInterface
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
         * The limit to the amount of likes you can retrieve from a post per cursor
         *
         * @var int
         */
        public $RetrieveLikesMaxLimit;

        /**
         * The limit to the amount of reposts you can retrieve from a post per cursor
         *
         * @var int
         */
        public $RetrieveRepostsMaxLimit;

        /**
         * The limit to the amount of likes you can retrieve from a post per cursor
         *
         * @var int
         */
        public $RetrieveRepliesMaxLimit;

        /**
         * The limit to the amount of quotes you can retrieve from a post per cursor
         *
         * @var int
         */
        public $RetrieveQuotesMaxLimit;

        /**
         * The limit to the amount of followers you can retrieve from a peer per cursor
         *
         * @var int
         */
        public $RetrieveFollowersMaxLimit;

        /**
         * The limit to the amount of following peers you can retrieve from a peer per cursor
         *
         * @var int
         */
        public $RetrieveFollowingMaxLimit;

        /**
         * The limit to the amount of posts you can retrieve from the feed per cursor
         *
         * @var int
         */
        public $RetrieveFeedMaxLimit;

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
                'retrieve_quotes_max_limit' => $this->RetrieveQuotesMaxLimit,
                'retrieve_followers_max_limit' => $this->RetrieveFollowersMaxLimit,
                'retrieve_following_max_limit' => $this->RetrieveFollowingMaxLimit,
                'retrieve_feed_max_limit' => $this->RetrieveFeedMaxLimit
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

            if(isset($data['retrieve_followers_max_limit']))
                $serverInformationObject->RetrieveFollowersMaxLimit = $data['retrieve_followers_max_limit'];

            if(isset($data['retrieve_following_max_limit']))
                $serverInformationObject->RetrieveFollowingMaxLimit = $data['retrieve_following_max_limit'];

            if(isset($data['retrieve_feed_max_limit']))
                $serverInformationObject->RetrieveFeedMaxLimit = $data['retrieve_feed_max_limit'];

            return $serverInformationObject;
        }

        /**
         * @inheritDoc
         */
        public static function getName(): string
        {
            return 'ServerInformation';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The ServerInformation object is a simple object that gives details about the server\'s attributes and limits or location of other servers that the client should communicate to for other purposes such as a CDN.';
        }

        /**
         * @inheritDoc
         */
        public static function getDefinition(): ObjectDefinition
        {
            return new ObjectDefinition(self::getName(), self::getDescription(), self::getParameters());
        }

        /**
         * @inheritDoc
         */
        public static function getParameters(): array
        {
            return [
                new ParameterDefinition('network_name', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The name of the network, eg; "Socialvoid"'),

                new ParameterDefinition('protocol_version', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The version of the protocol standard that the server is using, eg; "1.0"'),

                new ParameterDefinition('cdn_server', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The HTTP URL Endpoint for the CDN server of the network'),

                new ParameterDefinition('upload_max_file_size', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The maximum size of a file that you can upload to the CDN Server (in bytes)'),

                new ParameterDefinition('unauthorized_session_ttl', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The maximum time-to-live (in seconds) that an unauthorized session may have. The server will often reset the expiration whenever the session is used.'),

                new ParameterDefinition('authorized_session_ttl', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The maximum time-to-live (in seconds) that an authorized session may have. The server will often reset the expiration whenever the session is used.'),

                new ParameterDefinition('retrieve_likes_max_limit', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The maximum amount of likes a client can retrieve at once using the method timeline.get_likes via the page parameter'),

                new ParameterDefinition('retrieve_reposts_max_limit', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The maximum amount of reposts a client can retrieve at once using the method timeline.get_reposted_peers via the page parameter'),

                new ParameterDefinition('retrieve_replies_max_limit', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The maximum amount of replies a client can retrieve at once using the method timeline.get_replies via the page parameter'),

                new ParameterDefinition('retrieve_quotes_max_limit', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The maximum amount of quotes a client can retrieve at once using the method timeline.get_quotes via the page parameter'),

                new ParameterDefinition('retrieve_followers_max_limit', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The maximum amount of followers a client can retrieve at once using the method network.get_followers via the page parameter'),

                new ParameterDefinition('retrieve_following_max_limit', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The maximum amount of following peers a client can retrieve at once using the method network.get_following via the page parameter'),

                new ParameterDefinition('retrieve_feed_max_limit', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The amount of posts a client can retrieve at once using the method timeline.retrieve_feed via the page parameter'),

            ];
        }
    }
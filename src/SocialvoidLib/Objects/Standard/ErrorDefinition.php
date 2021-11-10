<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Types\BuiltinTypes;

    class ErrorDefinition
    {
        /**
         * The version of the protocol being used
         *
         * @var string
         */
        public $ProtocolVersion;

        /**
         * The name of the error definition
         *
         * @var string
         */
        public $Name;

        /**
         * The description of the error
         *
         * @var string
         */
        public $Description;

        /**
         * The error code associated with the error
         *
         * @var int
         */
        public $ErrorCode;

        /**
         * @param string|null $name
         * @param string|null $description
         * @param int|null $standard_error_code
         */
        public function __construct(?string $name=null, ?string $description=null, ?int $standard_error_code=null)
        {
            $this->ProtocolVersion = '1.0';
            $this->Name = $name;
            $this->Description = $description;
            $this->ErrorCode = $standard_error_code;
        }

        /**
         * @return string
         */
        public function getId(): string
        {
            return hash('crc32',  $this->ProtocolVersion . ':' . $this->Name . ':' . $this->ErrorCode);
        }

        /**
         * @return array
         */
        public function toArray(): array
        {
            return [
                'id' => $this->getId(),
                'name' => $this->Name,
                'description' => $this->Description,
                'error_code' => (int)$this->ErrorCode
            ];
        }

        /**
         * Returns an array representation of the error definition
         *
         * @param array $data
         * @return ErrorDefinition
         */
        public static function fromArray(array $data): ErrorDefinition
        {
            $definition = new ErrorDefinition();

            if(isset($data['name']))
                $definition->Name = $data['name'];

            if(isset($data['description']))
                $definition->Description = $data['description'];

            if(isset($data['error_code']))
                $definition->ErrorCode = (int)$data['error_code'];

            return $definition;
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
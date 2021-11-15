<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Types\BuiltinTypes;
    use SocialvoidLib\Interfaces\StandardObjectInterface;

    class ErrorDefinition implements StandardObjectInterface
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
            return 'ErrorDefinition';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The object ErrorDefinition contains information about an error that the server is capable of returning if a method fails to execute.';
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
                new ParameterDefinition('id', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The ID of the ErrorDefinition, which is a crc32 hash of the following value; <ProtocolVersion>:<ErrorName>:<ErrorCode> (1.0:InternalServerError:16384)'),

                new ParameterDefinition('name', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The name of the error, this is a unique value.'),

                new ParameterDefinition('description', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'A description of the error'),

                new ParameterDefinition('error_code', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The error code, this is a unique value.')
            ];
        }
    }
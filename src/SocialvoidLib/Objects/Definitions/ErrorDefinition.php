<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Definitions;

    class ErrorDefinition
    {
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
            $this->Name = $name;
            $this->Description = $description;
            $this->ErrorCode = $standard_error_code;
        }

        /**
         * @return string
         */
        public function getId(): string
        {
            return hash('crc32', $this->ErrorCode);
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
    }
<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Objects\Standard\ErrorDefinition;
    use SocialvoidLib\Objects\Standard\ObjectDefinition;

    class ProtocolDefinitions
    {
        /**
         * The version of the protocol
         *
         * @var string
         */
        public $Version;

        /**
         * An array of error definitions that the server uses
         *
         * @var ErrorDefinition[]
         */
        public $ErrorDefinitions;

        /**
         * An array of object definitions that the server uses
         *
         * @var ObjectDefinition[]
         */
        public $ObjectDefinitions;

        /**
         * Returns an array representation of the protocol definition object
         *
         * @return array[]
         */
        public function toArray(): array
        {
            $error_definitions = [];

            foreach($this->ErrorDefinitions as $definition)
                $error_definitions[] = $definition->toArray();

            $object_definitions = [];
            foreach($this->ObjectDefinitions as $definition)
                $object_definitions[] = $definition->toArray();

            return [
                'version' => $this->Version,
                'errors' => $error_definitions,
                'objects' => $object_definitions
            ];
        }
    }
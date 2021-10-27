<?php

    namespace SocialvoidLib\Objects;

    use SocialvoidLib\Objects\Definitions\ErrorDefinition;

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
         * Returns an array representation of the protocol definition object
         *
         * @return array[]
         */
        public function toArray(): array
        {
            $error_definitions = [];

            foreach($this->ErrorDefinitions as $definition)
                $error_definitions[] = $definition->toArray();

            return [
                'version' => $this->Version,
                'errors' => $error_definitions
            ];
        }
    }
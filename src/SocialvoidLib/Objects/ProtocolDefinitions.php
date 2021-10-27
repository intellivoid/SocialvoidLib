<?php

    namespace SocialvoidLib\Objects;

    use SocialvoidLib\Objects\Definitions\ErrorDefinition;

    class ProtocolDefinitions
    {
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
                'errors' => $error_definitions
            ];
        }
    }
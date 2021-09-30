<?php

    namespace SocialvoidLib\Interfaces;

    interface StandardObjectInterface
    {
        /**
         * Returns a schema structure of the object
         *
         * @return array
         */
        public function getSchema(): array;
    }
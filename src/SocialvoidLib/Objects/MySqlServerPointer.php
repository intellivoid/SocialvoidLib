<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects;

    class MySqlServerPointer
    {
        /**
         * @var string
         */
        public $HashPointer;

        /**
         * @var string
         */
        public $Name;

        /**
         * @var string
         */
        public $Host;

        /**
         * @var int
         */
        public $Port;

        /**
         * @var string
         */
        public $Database;

        /**
         * @var string|null
         */
        public $Username;

        /**
         * @var string|null
         */
        public $Password;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'hash_pointer' => $this->HashPointer,
                'name' => $this->Name,
                'host' => $this->Host,
                'port' => $this->Port,
                'database' => $this->Database,
                'username' => $this->Username,
                'password' => $this->Password
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return MySqlServerPointer
         */
        public static function fromArray(array $data): MySqlServerPointer
        {
            $MySqlServerPointer = new MySqlServerPointer();

            if(isset($data['hash_pointer']))
                $MySqlServerPointer->HashPointer = $data['hash_pointer'];

            if(isset($data['name']))
                $MySqlServerPointer->Name = $data['name'];

            if(isset($data['host']))
                $MySqlServerPointer->Host = $data['host'];

            if(isset($data['port']))
                $MySqlServerPointer->Port = $data['port'];

            if(isset($data['database']))
                $MySqlServerPointer->Database = $data['database'];

            if(isset($data['username']))
                $MySqlServerPointer->Username = $data['username'];

            if(isset($data['password']))
                $MySqlServerPointer->Password = $data['password'];

            return $MySqlServerPointer;
        }
    }
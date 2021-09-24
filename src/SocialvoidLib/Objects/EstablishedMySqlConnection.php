<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects;

    use mysqli;

    class EstablishedMySqlConnection
    {
        /**
         * @var mysqli|null
         */
        private $Connection;

        /**
         * @var MySqlServerPointer
         */
        public $MysqlServerPointer;

        /**
         * @var int
         */
        private $ReconnectTimeout;

        /**
         * @param MySqlServerPointer $mySqlServerPointer
         */
        public function __construct(MySqlServerPointer $mySqlServerPointer)
        {
            $this->MysqlServerPointer = $mySqlServerPointer;
            $this->Connection = null;
            $this->ReconnectTimeout = 0;
        }

        /**
         * Gets the connection state in a smart way
         *
         * @return mysqli
         */
        public function getConnection(): mysqli
        {
            if($this->Connection == null)
            {
                $this->Connection = new mysqli(
                    $this->MysqlServerPointer->Host, $this->MysqlServerPointer->Username, $this->MysqlServerPointer->Password,
                    $this->MysqlServerPointer->Database, $this->MysqlServerPointer->Port
                );

                $this->ReconnectTimeout = time() + 1800; // 30 Minutes

            }

            if(time() >= $this->ReconnectTimeout)
            {
                // Close and re-connect to prevent stale connection sockets
                $this->Connection->close();
                $this->Connection->connect(
                    $this->MysqlServerPointer->Host, $this->MysqlServerPointer->Username, $this->MysqlServerPointer->Password,
                    $this->MysqlServerPointer->Database, $this->MysqlServerPointer->Port
                );


            }

            return $this->Connection;
        }

        /**
         * Closes the database connection and releases used resources
         */
        public function disconnect()
        {
            if($this->Connection !== null)
            {
                $this->Connection->close();
                $this->ReconnectTimeout = 0;
                $this->Connection = null;
            }
        }

        /**
         * Determines if the connection to the database is alive or not
         *
         * @return bool
         */
        public function isAlive(): bool
        {
            if($this->Connection == null)
                return false;

            if(time() >= $this->ReconnectTimeout)
                return false;

            return true;
        }
    }
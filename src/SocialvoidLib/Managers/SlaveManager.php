<?php
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Managers;

    use mysqli;
    use SocialvoidLib\Exceptions\GenericInternal\DuplicateSlaveNameException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSlaveHashException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidStringFormatException;
    use SocialvoidLib\Objects\EstablishedMySqlConnection;
    use SocialvoidLib\Objects\MySqlServerPointer;
    use SocialvoidLib\SocialvoidLib;

    class SlaveManager
    {
        /**
         * @var SocialvoidLib
         */
        private $socialvoidLib;

        /**
         * @var EstablishedMySqlConnection[]
         */
        private $MySqlConnections;

        /**
         * @param SocialvoidLib $socialvoidLib
         * @throws DuplicateSlaveNameException
         * @throws InvalidStringFormatException
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
            $this->MySqlConnections = [];

            foreach($this->socialvoidLib->getSlaveServerConfiguration()['MySqlSlaves'] as $databaseSlave)
            {
                /** @noinspection PhpUnhandledExceptionInspection */
                $parsedSlave = self::createMySqlPointerFromString($databaseSlave);
                if(isset($this->getMySqlServer[$parsedSlave->HashPointer]))
                    throw new DuplicateSlaveNameException('One or more MySQL Slaves are named ' . $parsedSlave->Name);
                $this->MySqlConnections[$parsedSlave->HashPointer] = new EstablishedMySqlConnection($parsedSlave);
            }
        }

        /**
         * Returns a MySQL slave server connection
         *
         * @param string $hash
         * @return EstablishedMySqlConnection
         * @throws InvalidSlaveHashException
         */
        public function getMySqlServer(string $hash): EstablishedMySqlConnection
        {
            if(isset($this->MySqlConnections[$hash]) == false)
                throw new InvalidSlaveHashException('The slave hash \'' . $hash . '\' does not exist');

            return $this->MySqlConnections[$hash];
        }

        /**
         * Returns a random SQL Slave server, if prioritizing by alive then alive connections will be
         * selected first.
         *
         * @param bool $prioritize_alive
         * @return EstablishedMySqlConnection
         */
        public function getRandomMySqlServer(bool $prioritize_alive=true): EstablishedMySqlConnection
        {
            if($prioritize_alive)
            {
                $alive_connections = [];

                foreach($this->MySqlConnections as $hash => $connection)
                {
                    if($connection->isAlive())
                        $alive_connections[] = $hash;
                }

                if(count($alive_connections) > 0)
                {
                    return $this->MySqlConnections[$alive_connections[array_rand($alive_connections)]];
                }
            }

            return $this->MySqlConnections[array_rand($this->MySqlConnections)];
        }

        /**
         * Creates a pointer object for a MySQL Server
         *
         * @param string $name
         * @param string $host
         * @param int $port
         * @param string $database_name
         * @param string|null $username
         * @param string|null $password
         * @return MySqlServerPointer
         */
        public static function createMySqlPointer(string $name, string $host, int $port, string $database_name, ?string $username=null, ?string $password=null): MySqlServerPointer
        {
            $mysql_server_pointer = new MySqlServerPointer();
            $mysql_server_pointer->HashPointer = hash('ripemd128', $database_name);
            $mysql_server_pointer->Name = $name;
            $mysql_server_pointer->Host = $host;
            $mysql_server_pointer->Port = $port;
            $mysql_server_pointer->Database = $database_name;
            $mysql_server_pointer->Username = $username;
            $mysql_server_pointer->Password = $password;

            return $mysql_server_pointer;
        }

        /**
         * Create a MySQL pointer from a string
         *
         * @param string $input
         * @return MySqlServerPointer
         * @throws InvalidStringFormatException
         */
        public static function createMySqlPointerFromString(string $input): MySqlServerPointer
        {
            $re = '/(?<type>\w+):\/\/(?<servername>\w+):(?<username>\w+):(?<password>\w+)\@(?<host>.+):(?<port>\d+)\/(?<database>\w+)/m';
            preg_match($re, $input, $matches);

            if(count($matches) == 0)
                throw new InvalidStringFormatException('The correct string format is \'mysql://servername:user:password@127.0.0.1:20801/db_name\'');

            return self::createMySqlPointer(
                urldecode($matches['servername']),
                urldecode($matches['host']),
                (int)urldecode($matches['port']),
                urldecode($matches['database']),
                urldecode($matches['username']),
                urldecode($matches['password'])
            );
        }
    }
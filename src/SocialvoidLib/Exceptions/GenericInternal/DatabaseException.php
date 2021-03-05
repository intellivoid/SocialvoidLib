<?php


    namespace SocialvoidLib\Exceptions\GenericInternal;


    use Exception;
    use mysqli;
    use Throwable;

    /**
     * Class DatabaseException
     * @package SocialvoidLib\Exceptions\GenericInternal
     */
    class DatabaseException extends Exception
    {
        /**
         * @var string
         */
        private string $query;

        /**
         * @var string
         */
        private string $database_error;

        /**
         * @var mysqli|null
         */
        private ?mysqli $mysqli;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * DatabaseException constructor.
         * @param string $message
         * @param string $query
         * @param string $database_error
         * @param mysqli|null $mysqli
         * @param Throwable|null $previous
         */
        public function __construct($message = "", string $query="", string $database_error="", mysqli $mysqli=null, Throwable $previous = null)
        {
            parent::__construct($message, 0, $previous);
            $this->message = $message;
            $this->query = $query;
            $this->database_error = $database_error;
            $this->mysqli = $mysqli;
            $this->previous = $previous;
        }

        /**
         * @return string
         */
        public function getDatabaseError(): string
        {
            return $this->database_error;
        }

        /**
         * @return string
         */
        public function getQuery(): string
        {
            return $this->query;
        }

        /**
         * @return mysqli|null
         */
        public function getMysqli(): ?mysqli
        {
            return $this->mysqli;
        }
    }
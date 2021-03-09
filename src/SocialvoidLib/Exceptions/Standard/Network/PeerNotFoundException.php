<?php


    namespace SocialvoidLib\Exceptions\Standard\Network;


    use Exception;
    use SocialvoidLib\Abstracts\StandardErrorCodes;
    use Throwable;

    /**
     * Class PeerNotFoundException
     * @package SocialvoidLib\Exceptions\Standard\Network
     */
    class PeerNotFoundException extends Exception
    {
        /**
         * @var string|null
         */
        private ?string $search_by;

        /**
         * @var string|null
         */
        private ?string $search_value;

        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * PeerNotFoundException constructor.
         * @param string $message
         * @param string|null $search_by
         * @param string|null $search_value
         * @param Throwable|null $previous
         */
        public function __construct($message = "", string $search_by=null, string $search_value=null, Throwable $previous = null)
        {
            parent::__construct($message, StandardErrorCodes::PeerNotFoundException, $previous);
            $this->message = $message;
            $this->search_by = $search_by;
            $this->search_value = $search_value;
            $this->previous = $previous;
        }

        /**
         * @return string|null
         */
        public function getSearchBy(): ?string
        {
            return $this->search_by;
        }

        /**
         * @return string|null
         */
        public function getSearchValue(): ?string
        {
            return $this->search_value;
        }
    }
<?php


    namespace SocialvoidLib\Exceptions\Internal;


    use Exception;
    use SocialvoidLib\Abstracts\InternalErrorCodes;
    use Throwable;

    /**
     * Class EntityWithoutAccessException
     * @package SocialvoidLib\Exceptions\Internal
     */
    class EntityWithoutAccessException extends Exception
    {

        /**
         * @var string
         */
        private string $entity_type;

        /**
         * @var string
         */
        private string $entity_identifier;

        /**
         * @var array
         */
        private array $data;

        /**
         * EntityWithoutAccessException constructor.
         * @param string $entity_type
         * @param string $entity_identifier
         * @param array $data
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct(string $entity_type, string $entity_identifier, array $data, $message = "", Throwable $previous = null)
        {
            parent::__construct($message, InternalErrorCodes::EntityWithoutAccessException, $previous);
            $this->message = $message;
            $this->entity_type = $entity_type;
            $this->entity_identifier = $entity_identifier;
            $this->data = $data;
        }
    }
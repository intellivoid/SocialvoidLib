<?php


    namespace SocialvoidLib\Exceptions\GenericInternal;


    use Exception;
    use SocialvoidLib\ServiceJobs\ServiceJobQuery;
    use Throwable;

    /**
     * Class ServiceJobException
     * @package SocialvoidLib\Exceptions\GenericInternal
     */
    class ServiceJobException extends Exception
    {
        /**
         * @var ServiceJobQuery|null
         */
        private ?ServiceJobQuery $job;


        /**
         * ServiceJobException constructor.
         * @param string $message
         * @param ServiceJobQuery|null $job
         * @param Throwable|null $previous
         */
        public function __construct($message = "", ServiceJobQuery $job=null, Throwable $previous = null)
        {
            parent::__construct($message, 0, $previous);
            $this->message = $message;
            $this->job = $job;
        }
    }
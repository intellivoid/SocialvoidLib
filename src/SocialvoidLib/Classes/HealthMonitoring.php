<?php

    namespace SocialvoidLib\Classes;

    use SocialvoidLib\Abstracts\StatusStates\HealthStatusCode;
    use SocialvoidLib\Exceptions\Internal\InvalidHealthStatusCodeException;
    use SocialvoidLib\SocialvoidLib;

    class HealthMonitoring
    {
        /**
         * @var string
         */
        private string $WorkingDirectory;

        /**
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->WorkingDirectory = $socialvoidLib->getWorkingLocationPath();
            if(is_dir($this->WorkingDirectory))
                mkdir($this->WorkingDirectory);
            $this->WorkingDirectory = $this->WorkingDirectory . DIRECTORY_SEPARATOR . 'health';
            if(is_dir($this->WorkingDirectory))
                mkdir($this->WorkingDirectory);
        }

        /**
         * @param string $module_name
         * @param int $status_code
         * @param int $timeout
         * @throws InvalidHealthStatusCodeException
         */
        public function sync(string $module_name, int $status_code, int $timeout=10)
        {
            switch($status_code)
            {
                case HealthStatusCode::Starting:
                case HealthStatusCode::Ok:
                case HealthStatusCode::Failing:
                case HealthStatusCode::Fatal:
                case HealthStatusCode::Terminated:
                    break;
                default:
                    throw new InvalidHealthStatusCodeException('The given health status code \'' . $status_code . '\' is invalid');
            }

            $sync_status = self::generateSyncStatus(time(), $timeout, $status_code);
            $heartbeat_path = $this->WorkingDirectory . DIRECTORY_SEPARATOR . $module_name;

            file_put_contents($heartbeat_path, $sync_status);
        }

        /**
         * Generates a simple syntax for the health state
         *
         * @param int $timestamp
         * @param int $timeout
         * @param int $status_code
         * @return string
         */
        public static function generateSyncStatus(int $timestamp, int $timeout, int $status_code): string
        {
            return "$timestamp:$timeout:$status_code";
        }
    }
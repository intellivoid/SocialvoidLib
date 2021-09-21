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
         * @var array
         */
        private static $MonitoringCache;

        /**
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            if(self::$MonitoringCache == null)
                self::$MonitoringCache = [];
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
            if(isset(self::$MonitoringCache[$module_name]))
            {
                $cache_timeout = (int)self::$MonitoringCache[$module_name];
                if($cache_timeout > 5)
                {
                    // Prevent writing to disk all the time
                    if(time() < (self::$MonitoringCache[$module_name] - 5))
                        return;
                }
            }

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
            self::$MonitoringCache[$module_name] = time() + $timeout;
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
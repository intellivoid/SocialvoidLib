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
         * @var array
         */
        private static $MonitoringStatusCache;

        /**
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            if(self::$MonitoringCache == null)
                self::$MonitoringCache = [];
            if(self::$MonitoringStatusCache == null)
                self::$MonitoringStatusCache = [];
            $this->WorkingDirectory = $socialvoidLib->getWorkingLocationPath();
            if(is_dir($this->WorkingDirectory) == false)
                mkdir($this->WorkingDirectory);
            $this->WorkingDirectory = $this->WorkingDirectory . DIRECTORY_SEPARATOR . 'health';
            if(is_dir($this->WorkingDirectory) == false)
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
                if(isset(self::$MonitoringStatusCache[$module_name]) && self::$MonitoringStatusCache[$module_name] == $status_code)
                {
                    $cache_timeout = (int)self::$MonitoringCache[$module_name];
                    if($cache_timeout > 5)
                    {
                        // Prevent writing to disk all the time
                        if(time() < (self::$MonitoringCache[$module_name] - 5))
                            return;
                    }
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
            self::$MonitoringStatusCache[$module_name] = $status_code;
        }

        /**
         * Returns the health status of the module
         *
         * @param string $module_name
         * @param bool $determine_correct_status_code
         * @return int
         */
        public function getHealthStatus(string $module_name, bool $determine_correct_status_code=true): int
        {
            $heartbeat_path = $this->WorkingDirectory . DIRECTORY_SEPARATOR . $module_name;
            if(file_exists($heartbeat_path) == false)
                return HealthStatusCode::Terminated;

            $status = explode(':', file_get_contents($heartbeat_path));
            if(count($status) !== 3)
                return HealthStatusCode::Fatal;

            // If timeout is enabled and the correct status code is to be determined
            if($status[1] > 0 && $determine_correct_status_code)
            {
                if((time() - $status[0]) > $status[1])
                    return HealthStatusCode::Terminated;
            }

            return $status[2];
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
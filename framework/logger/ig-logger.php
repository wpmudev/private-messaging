<?php
/**
 * @author:Hoang Ngo
 */

if (!class_exists('IG_Logger')) {
    class IG_Logger
    {
        const ERROR_LEVEL_INFO = 'INFO', ERROR_LEVEL_WARNING = 'WARNING', ERROR_LEVEL_ERROR = 'ERROR', ERROR_LEVEL_DEBUG = 'DEBUG';

        public $type;
        public $location;

        public function __construct($type, $location)
        {
            $this->type = $type;
            $path = dirname(dirname(__FILE__)) . '/runtime/' . $location;
            $this->location = $path;
        }

        public function log($message, $level = self::ERROR_LEVEL_INFO)
        {
            //build log message
            $log = sprintf("[%s] [%s] %s", date('Y-m-d H:i:s'), $level, $message);
            $log = $log . PHP_EOL;
            if ($this->type == 'db') {
                $this->_db_log($log);
            } else {
                $this->_file_log($log);
            }
        }

        private function _db_log($message)
        {
            $logs = get_option($this->location);
            if (!$logs) {
                $logs = '';
            }
            $logs .= $message;
            update_option($this->location, $logs);
        }

        private function _file_log($message)
        {
            if (!file_exists($this->location)) {
                $handle = fopen($this->location, 'w');
            } else {
                $handle = fopen($this->location, 'a');
            }

            if ($handle) {
                fwrite($handle, $message);
                fclose($handle);
            }
        }
    }
}
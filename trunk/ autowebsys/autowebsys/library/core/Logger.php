<?php

require_once 'Log.php';

class Logger {

    private static $logger = null;

    public function __construct() {
        $this->getLogger();
    }

    public function errorHandler($code, $message, $file, $line) {
        switch ($code) {
            case E_WARNING:
            case E_USER_WARNING:
                $priority = PEAR_LOG_WARNING;
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $priority = PEAR_LOG_NOTICE;
                break;
            case E_ERROR:
            case E_USER_ERROR:
                $priority = PEAR_LOG_ERR;
                break;
            default:
                $priority = PEAR_LOG_INFO;
        }
        $this->log("PHP", $message . ' in ' . $file . ' at line ' . $line, $priority);
    }

    public static function getLogPath() {
        return APPLICATION_PATH . "/../../logs/";
    }

    private static function getLogger() {
        if (self::$logger == null) {
            $conf = array('mode' => 0644, 'timeFormat' => '%X %x');
            self::$logger = Log::singleton('file', self::getLogPath()."log.log", '', $conf);
            $mask = PEAR_LOG_ALL ^ Log::MASK(PEAR_LOG_DEBUG);
            self::$logger->setMask($mask);
        }
        return self::$logger;
    }

    public static function log($type, $message, $level) {
        $logger = self::getLogger();
        $logger->setIdent($type);
        $logger->log($message, $level);
    }

    public static function notice($type, $message) {
        self::log($type, $message, PEAR_LOG_NOTICE);
    }

    public static function info($type, $message) {
        self::log($type, $message, PEAR_LOG_INFO);
    }

    public static function warning($type, $message) {
        self::log($type, $message, PEAR_LOG_WARNING);
    }

    public static function alert($type, $message) {
        self::log($type, $message, PEAR_LOG_ALERT);
    }

}
?>

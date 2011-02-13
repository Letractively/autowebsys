<?php

require_once('core/AuthManager.php');
require_once('core/Logger.php');

class StreamerController extends Zend_Controller_Action {

    private static $log_type = "CONTROLLER_STREAMER";

    private function getRoot() {
        return $root = APPLICATION_PATH . "/../library/";
    }

    public function indexAction() {
        $type = $this->_getParam("type");
        $path = $this->_getParam("path");
        if (strpos($path, "..")) {
            Logger::warning(self::$log_type, "User " . AuthManager::getUsername() . " tried to access forbidden file: " . $path);
        } else {
            switch ($type) {
                case "js":
                    $this->streamJS($path);
                    break;
                case "css":
                    $this->streamCSS($path);
                    break;
            }
        }
    }

    private function streamJS($path) {
        $headers = array();
        $headers[] = 'Content-type: application/javascript';
        $this->streamFile($headers, $this->getRoot() . "js/" . $path);
    }

    private function streamCSS($path) {
        $headers = array();
        $headers[] = 'Content-type: text/css';
        $this->streamFile($headers, $this->getRoot() . "css/" . $path);
    }

    private function streamFile($headers, $path) {
        if (file_exists ($path)) {
            foreach ($headers as $header) {
                header($header);
            }
            readfile($path);
        } else {
            Logger::warning(self::$log_type, "User " . AuthManager::getUsername() . " tried to access non existing file: " . $path);
        }
    }

}

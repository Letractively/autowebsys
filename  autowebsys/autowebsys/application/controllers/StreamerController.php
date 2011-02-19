<?php

require_once('core/FileManager.php');
require_once('core/AuthManager.php');
require_once('core/Logger.php');

class StreamerController extends Zend_Controller_Action {

    private static $log_type = "CONTROLLER_STREAMER";

    public function indexAction() {
        $type = $this->_getParam("type");
        switch($type) {
            case "getjs":
                $name = $this->_getParam("name");
                $this->streamFile(FileManager::getJSPath() . $name, array("Content-type: application/javascript"));
            default:
                Logger::warning(self::$log_type, "Unknown action type: " . $type);
        }
    }

    private function streamFile($path, $headers) {
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

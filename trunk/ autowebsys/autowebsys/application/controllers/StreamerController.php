<?php

require_once('core/FileManager.php');
require_once('core/auth/AuthManager.php');
require_once('core/Logger.php');

/**
 * Kontroler wysyłający pliki js i css, do których wymagana jest autoryzacja.
 * Trzeba by wywalić tego switcha
 * @author Tomasz 'lobo' Kopacki
 * @email tomasz@kopacki.eu
 */
class StreamerController extends Zend_Controller_Action {

    private static $log_type = "CONTROLLER_STREAMER";

    public function indexAction() {
        $type = $this->_getParam("type");
        switch ($type) {
            case "js":
                $name = $this->_getParam("name");
                $this->streamFile(FileManager::getJSPath() . $name, array("Content-type: text/javascript"));
                break;
            case "css":
                $name = $this->_getParam("name");
                $this->streamFile(FileManager::getCSSPath() . $name, array("Content-type: text/css"));
                break;
            default:
                Logger::warning(self::$log_type, "Unknown action type: " . $type);
        }
    }

    /**
     * Funkcja wyrzuca na wyjście plik podany w parametrze z zadanymi
     * nagłówkami HTTP
     * @param string $path ścieżka do pliku, który ma być wysłany
     * @param array $headers nagłówki HTML, które mają być wysłane
     */
    private function streamFile($path, $headers) {
        if (file_exists($path)) {
            foreach ($headers as $header) {
                header($header);
            }
            readfile($path);
        } else {
            Logger::warning(self::$log_type, "User " . AuthManager::getUsername() . " tried to access non existing file: " . $path);
        }
    }

}

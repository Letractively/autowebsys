<?php

require_once("core/AuthManager.php");
require_once("core/Logger.php");
require_once("core/MemcacheManager.php");
require_once("core/XMLParser.php");

class ApplicationManager {

    private static $memcache;
    private static $log_type = "APP_CONF";
    const DB_CREDENTIALS = "application.db.credentials";
    const DB_QUERY = "application.db.queries.";
    const INTERFACE_MAINMENU = "application.interface.menu";
    const WINDOW_DESCRIPTION = "application.windows.description.";
    const WINDOW_CONTENT = "application.windows.content.";
    const PARAMETER = "application.parameters.";
    const STATUS = "application.status";
    const CACHE = "application.parameters.cache";
    const DATA_MODEL_SQL = "application.models.";
    const DATA_TEMPLATE = "application.templates.";

    public static function initApplication() {
        date_default_timezone_set('Europe/Warsaw');
        set_error_handler(array(new Logger(), "errorHandler"));
        $memcache = new MemcacheManager();
        XMLParser::checkCacheStatus($memcache);
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(AuthManager::setAdapter(ApplicationManager::getCachedValue(ApplicationManager::PARAMETER, "authAdapter")), 0);
    }

    private static function getMemcache() {
        if (ApplicationManager::$memcache == null) {
            ApplicationManager::$memcache = new MemcacheManager();
        }
        return ApplicationManager::$memcache;
    }

    public static function getCachedValue($type, $name = "") {
        $query = $type . $name;
        XMLParser::checkCacheStatus(ApplicationManager::getMemcache());
        $result = ApplicationManager::getMemcache()->get($query);
        if($result == "") {
            Logger::alert(self::$log_type, "Value " . $query . " not found");
        }
        return $result;
    }

}
?>

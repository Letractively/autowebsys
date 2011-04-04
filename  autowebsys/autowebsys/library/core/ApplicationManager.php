<?php

require_once("core/AuthManager.php");
require_once("core/Logger.php");
require_once("core/MemcacheManager.php");
require_once("core/XMLParser.php");

class ApplicationManager {

    private static $memcache;
    private static $log_type = "APP_CONF";
    public static $DB_CREDENTIALS = "application.db.credentials";
    public static $DB_QUERY = "application.db.queries.";
    public static $INTERFACE_MAINMENU = "application.interface.menu";
    public static $WINDOW_DESCRIPTION = "application.windows.description.";
    public static $WINDOW_CONTENT = "application.windows.content.";
    public static $PARAMETER = "application.parameters.";
    public static $STATUS = "application.status";
    public static $CACHE = "application.parameters.cache";
    public static $DATA_MODEL_SQL = "application.models.";
    public static $DATA_TEMPLATE = "application.templates.";
    public static $ST_TAG = "application.tags.";

    public static function initApplication() {
        date_default_timezone_set('Europe/Warsaw');
        set_error_handler(array(new Logger(), "errorHandler"));
        self::setPrefix();
        $memcache = new MemcacheManager();
        XMLParser::checkCacheStatus($memcache);
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(AuthManager::setAdapter(ApplicationManager::getCachedValue(ApplicationManager::$PARAMETER, "authAdapter")), 0);
    }

    private static function setPrefix() {
        $prefix = APPLICATION_PATH;
        self::$DB_CREDENTIALS = $prefix . self::$DB_CREDENTIALS;
        self::$DB_QUERY = $prefix . self::$DB_QUERY;
        self::$INTERFACE_MAINMENU = $prefix . self::$INTERFACE_MAINMENU;
        self::$WINDOW_DESCRIPTION = $prefix . self::$WINDOW_DESCRIPTION;
        self::$WINDOW_CONTENT = $prefix . self::$WINDOW_CONTENT;
        self::$PARAMETER = $prefix . self::$PARAMETER;
        self::$STATUS = $prefix . self::$STATUS;
        self::$CACHE = $prefix . self::$CACHE;
        self::$DATA_MODEL_SQL = $prefix . self::$DATA_MODEL_SQL;
        self::$DATA_TEMPLATE = $prefix . self::$DATA_TEMPLATE;
        self::$ST_TAG = $prefix . self::$ST_TAG;
        Logger::notice(self::$log_type, "Prefix " . $prefix . " set up");
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
        if ($result == "") {
            Logger::alert(self::$log_type, "Value " . $query . " not found");
        }
        return $result;
    }

}
?>

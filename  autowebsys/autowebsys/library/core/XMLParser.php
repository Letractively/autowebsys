<?php

require_once('core/renderers/MainMenuRenderer.php');
require_once('core/renderers/WindowRenderer.php');
require_once('core/ApplicationManager.php');

class XMLParser {

    private static $memcache;
    private static $log_type = "XML";

    private static function parseRoot($xml) {
        self::parseXML($xml, array(
                    'data' => array(
                        'datasource' => 'handleDatasource',
                        'queries' => array(
                            'query' => 'handleQuery',
                        ),
                    ),
                    'parameters' => array(
                        'param' => 'handleParam',
                    ),
                    'interface' => array(
                        'main-menu' => 'handleMainmenu',
                    ),
                    'windows' => array(
                        'window' => 'handleWindow',
                    ),
                    'models' => array(
                        'model' => 'handleModel',
                    ),
                    'templates' => array(
                        'template' => 'handleTemplate',
                    ),
                    'tags' => array(
                        'tag' => 'handleTag',
                    ),
                    'security' => array(
                        'groups' => array(
                            'group' => 'handleGroup',
                        ),
                        'roles' => array(
                            'role' => 'handleRole'
                        ),
                    ),
                ));
        return true;
    }

    private static function handleGroup($xml) {
        $group = array('id' => $xml['id']->__toString());
        if (isset($xml['parent'])) {
            $group['parent'] = $xml['parent']->__toString();
        }
        self::$memcache->set(ApplicationManager::$SECURITY_GROUPS . $xml['id'], $group);
        Logger::notice(self::$log_type, "Security group " . $xml['id'] . " cached");
    }

    private static function handleRole($xml) {
        $group = array(
            'id' => $xml['id']->__toString(),
            'group' => $xml['group']->__toString(),
            'password' => $xml['password']->__toString(),
        );
        self::$memcache->set(ApplicationManager::$SECURITY_ROLES . $xml['id'], $group);
        Logger::notice(self::$log_type, "Security role " . $xml['id'] . " cached");
    }

    private static function handleTemplate($xml) {
        $template = array();
        self::$memcache->set(ApplicationManager::$DATA_TEMPLATE . $xml->name, $xml->asXML());
        Logger::notice(self::$log_type, "Template " . $xml->name . " cached");
    }

    private static function handleTag($xml) {
        $template = array();
        self::$memcache->set(ApplicationManager::$ST_TAG . $xml->name, $xml->asXML());
        Logger::notice(self::$log_type, "Tag " . $xml->name . " cached");
    }

    private static function handleModel($xml) {
        $model = array();
        self::$memcache->set(ApplicationManager::$DATA_MODEL_SQL . $xml->name, $xml->asXML());
        Logger::notice(self::$log_type, "Model " . $xml->name . " cached");
    }

    private static function handleValidators($validators) {
        $vArray = array();
        foreach ($validators->children() as $validator) {
            $vArray[$validator['column']->__toString()] = $validator->__toString();
        }
        return $vArray;
    }

    private static function handleDatasource($xml) {
        $credentials = array('url' => $xml['url']->__toString(),
            'user' => $xml['user']->__toString(),
            'password' => $xml['password']->__toString());
        self::$memcache->set(ApplicationManager::$DB_CREDENTIALS, $credentials);
        Logger::notice(self::$log_type, "DB credentials cached");
    }

    private static function handleQuery($xml) {
        self::$memcache->set(ApplicationManager::$DB_QUERY . $xml['name']->__toString(), $xml->__toString());
        Logger::notice(self::$log_type, "Query " . $xml['name'] . " cached");
    }

    private static function handleParam($xml) {
        self::$memcache->set(ApplicationManager::$PARAMETER . $xml['name']->__toString(), $xml->__toString());
        Logger::notice(self::$log_type, "Parameter " . $xml['name'] . " cached");
    }

    private static function handleWindow($xml) {
        $content = WindowRenderer::renderContent($xml->content);
        unset($xml->content);
        $description = $xml;
        self::$memcache->set(ApplicationManager::$WINDOW_DESCRIPTION . $xml['id']->__toString(), $description->asXML());
        self::$memcache->set(ApplicationManager::$WINDOW_CONTENT . $xml['id']->__toString(), $content);
        Logger::notice(self::$log_type, "Window " . $xml['id'] . " cached");
    }

    private static function handleMainmenu($xml) {
        self::$memcache->set(ApplicationManager::$INTERFACE_MAINMENU, $xml->asXML());
        Logger::notice(self::$log_type, "Mainmenu cached");
    }

    private static function getRootFolder() {
        return APPLICATION_PATH . "/../../xmls/";
    }

    public static function checkCacheStatus($memcache) {
        self::$memcache = $memcache;
        $status = self::$memcache->get(ApplicationManager::$STATUS);
        $cache = self::$memcache->get(ApplicationManager::$CACHE);
        if (!$status || !$cache || $cache == 'false') {
            Logger::notice(self::$log_type, "Configuration not cached or cache disabled, parsing");
            $start = time();
            if (self::parseRoot(self::getXML("configuration.xml"))) {
                self::$memcache->set(ApplicationManager::$STATUS, '1');
                Logger::notice(self::$log_type, "Configuration cached in " . (time() - $start) . "ms");
            } else {
                Logger::alert(self::$log_type, "XML configuration error");
            }
        }
    }

    private static function getXML($path) {
        $path = self::getRootFolder() . $path;
        $xml = simplexml_load_file($path);
        if (!$xml) {
            Logger::alert(self::$log_type, "Can't find config file: " . $path);
            return null;
        } else {
            Logger::notice(self::$log_type, 'Parsing xml file: ' . $path);
        }
        return $xml;
    }

    private static function parseXML($xml, $map) {
        foreach ($xml->children() as $child) {
            switch ($child->getName()) {
                case "include":
                    self::parseXML(self::getXML($child['path']), $map);
                    break;
                default:
                    if (isset($map[$child->getName()])) {
                        $el = $map[$child->getName()];
                        if (is_array($el)) {
                            self::parseXML($child, $el);
                        } else if (is_string($el)) {
                            self::call(array('XMLParser', $el), array($child));
                        }
                    } else {
                        Logger::alert(self::$log_type, "Tag '" . $child->getName() . "' unknown");
                    }
            }
        }
    }

    public static function call($aCallable, $parameters) {
        if (!is_callable($aCallable)) {
            Logger::alert(self::$log_type, $aCallable[0] . "->" . $aCallable[1] . " is not callable");
            return null;
        } else {
            return call_user_func_array($aCallable, $parameters);
        }
    }

    public static function xmlStringAsObject($xmlString) {
        return simplexml_load_string($xmlString);
    }

    public static function getModel($modelName) {
        $model = ApplicationManager::getCachedValue(ApplicationManager::$DATA_MODEL_SQL, $modelName);
        return simplexml_load_string($model);
    }

    public static function getWindowDescription($modelName) {
        $model = ApplicationManager::getCachedValue(ApplicationManager::$WINDOW_DESCRIPTION, $modelName);
        return simplexml_load_string($model);
    }

}
?>

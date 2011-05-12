<?php

require_once('core/ApplicationManager.php');
require_once('core/Logger.php');

class STParser {

    private static $log_type = "STPARSER";

    public static function parse($string, $requestParams = array()) {
        Logger::notice(self::$log_type, "parsing:" . $string);
        $out = "";
        $start = 0;
        $offset = 0;
        while (!(($offset = strpos($string, "\${", $start)) === false)) {
            $out .= substr($string, $start, $offset - $start);
            $endTag = strpos($string, "}", $offset);
            $tag = substr($string, $offset, $endTag - $offset + 1);
            $start = $endTag + 1;
            $out .= self::parseTag($tag, $requestParams);
        }
        $out .= substr($string, $start);
        Logger::notice(self::$log_type, "parsed to: " . $out);
        return $out;
    }

    private static function parseTag($tag, $requestParams) {
        $startName = 2;
        $endName = strpos($tag, "(");
        $tagName = substr($tag, $startName, $endName - $startName);
        $params = substr($tag, $endName + 1, -2);
        $params = str_replace(" ", "", $params);
        Logger::notice(self::$log_type, "Found tag:" . $tagName . " with parameters (" . $params . ")");
        $params = explode(",", $params);
        return self::executeTag($tagName, $params, $requestParams);
    }

    private static function executeTag($name, $params, $requestParams) {
        $xml = ApplicationManager::getCachedValue(ApplicationManager::$ST_TAG, $name);
        $xmlModel = simplexml_load_string($xml);
        $className = $xmlModel->class->__toString();
        require_once("tags/" . $className . ".php");
        $object = new $className;
        $object->setRequestParams($requestParams);
        $method = $xmlModel->method->__toString();
        return $object->$method($params);
    }

}
?>

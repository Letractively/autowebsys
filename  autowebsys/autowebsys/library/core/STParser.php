<?php

require_once('core/Translator.php');
require_once('core/Logger.php');
require_once('core/renderers/WindowRenderer.php');
require_once('core/renderers/ModelRenderer.php');

class STParser {
    private static $log_type = "STPARSER";

    public static function parse($string, $params = array()) {
        Logger::notice(self::$log_type, "parsing:".$string);
        $out = "";
        while(strstr($string, "{")) {
            $before_pos = strpos($string, "{");
            $out .= substr($string, 0, $before_pos);
            $after_pos = strpos($string, "}");
            $key = substr($string, $before_pos, $after_pos - $before_pos + 1);
            $tag = substr($key, 1, strpos($key, ":") - 1);
            $expression = substr($key, strpos($key, ":") + 1, -1);
            $string = substr($string, $after_pos + 1);
            switch($tag) {
                case "translator":
                    $out .= Translator::getText($expression);
                    break;
                case "call":
                    $out .= self::parseCustomCall($expression);
                    break;
                case "controller":
                    $out .= self::parseCustomController($expression);
                    break;
                case "model":
                    $out .= self::parseModel($expression, $params);
                    break;
                default:
                    Logger::warning(self::$log_type, "Unknown ST tag: " . $tag);
            }
        }
        $out .= $string;
        Logger::notice(self::$log_type, "parsed to: ".$out);
        return $out;
    }

    private static function parseCustomCall($expression) {
        $controllerNameStart = 0;
        $controllerNameEnd = strpos($expression, ".");
        $controllerName = substr($expression, $controllerNameStart, $controllerNameEnd - $controllerNameStart);
        $methodNameStart = $controllerNameEnd + 1;
        $methodNameEnd = $controllerNameEnd = strpos($expression, "(");
        $methodName = substr($expression, $methodNameStart, $methodNameEnd - $methodNameStart);
        $paramsStart = strpos($expression, "(") + 1;
        $paramsEnd = strpos($expression, ")");
        $params = substr($expression, $paramsStart, $paramsEnd - $paramsStart);
        Logger::notice(self::$log_type, "Custom call: " . $controllerName . " params: " . $params);
        $params = explode(",", $params);
        require_once("utils/" . $controllerName . ".php");
        return XMLParser::call(array($controllerName, $methodName), $params);
    }

    private static function parseCustomController($expression) {
        $controllerName = substr($expression, 0, strpos($expression, "."));
        $actionName = substr($expression, strpos($expression, ".") + 1);
        $uid = WindowRenderer::getUID();
        $out = "<div id=\"$uid\"></div>";
        $out .= "<script type=\"text/javascript\">";
        $out .= "var el = document.getElementById('$uid');";
        $out .= "el.innerHTML = dhtmlxAjax.getSync('/custom/index/class/$controllerName/method/$actionName').xmlDoc.responseText;";
        $out .= "</script>";
        return $out;
    }

    private static function parseModel($expression, $params) {
        $out = "";
        $type = substr($expression, 0, strpos($expression, "("));
        $name = substr($expression, strpos($expression, "(") + 1, -1);
        $model = ApplicationManager::getCachedValue(ApplicationManager::DATA_MODEL_SQL, $name);
        switch($type) {
            case "grid":
                $out .= ModelRenderer::renderGrid($model);
                break;
            case "form":
                if(isset($params['id'])) {
                    $out .= ModelRenderer::renderForm($model, $params['id']);
                } else {
                    $out .= ModelRenderer::renderForm($model);
                }
                break;
            default:
                Logger::warning(self::$log_type, "Uknown model type: " . $type);
        }
        return $out;
    }
}
?>

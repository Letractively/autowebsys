<?php

abstract class CustomTag {

    private $requestParams;

    public function getRequestParams() {
        return $this->requestParams;
    }

    public function setRequestParams($requestParams) {
        $this->requestParams = $requestParams;
    }

    public function hasRequestParam($name) {
        return isset($this->requestParams[$name]);
    }

    public function getRequestParam($name) {
        return $this->requestParams[$name];
    }

    public static function flatRequestParams($paramters) {
        $out = "";
        foreach ($paramters as $name => $value) {
            if (self::notRestricted($name)) {
                $out .= "/$name/$value";
            }
        }
        return $out;
    }

    private static function notRestricted($name) {
        return!in_array($name, array("controller", "action", "module", "type", "subtype", "name"));
    }

}
?>

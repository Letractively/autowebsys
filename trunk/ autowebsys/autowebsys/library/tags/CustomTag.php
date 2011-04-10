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

    public function flatRequestParams() {
        $out = "";
        foreach($this->requestParams as $name => $value) {
            if($out == "") {
                $out .= "?$name=$value";
            } else {
                $out .= "&$name=$value";
            }
        }
        return $out;
    }
}

?>

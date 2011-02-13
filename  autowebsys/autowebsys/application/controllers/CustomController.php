<?php

require_once("core/XMLParser.php");

class CustomController extends Zend_Controller_Action {

    public function indexAction() {
        $class = $this->_getParam("class");
        $method = $this->_getParam("method");
        require_once("controllers/" . $class . ".php");
        echo XMLParser::call(array($class, $method), array());
    }
}

?>

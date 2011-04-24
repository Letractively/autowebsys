<?php

require_once("core/XMLParser.php");

class CustomController extends Zend_Controller_Action {

    public function indexAction() {
        $name = $this->_getParam("name");
        $model = XMLParser::getController($name);
        if (AuthManager::checkAccess($model->security, $this->getRequest())) {
            $xml = XMLParser::xmlStringAsObject(ApplicationManager::getCachedValue(ApplicationManager::$CUSTOM_CONTROLLER, $name));
            $class = $xml->class->__toString();
            Logger::notice("CUSTOM_CONTROLLER", "Executing custom controller: " . $xml->name);
            require_once("controllers/" . $class . ".php");
            $instance = new $class();
            $instance->setController($this);
            echo $instance->handleRequest();
        }
    }

}
?>

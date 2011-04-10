<?php

require_once('core/ApplicationManager.php');
require_once('core/DBManager.php');
require_once('core/Logger.php');
require_once('core/renderers/MainMenuRenderer.php');
require_once('core/renderers/WindowRenderer.php');

class DataController extends Zend_Controller_Action {

    private static $log_type = "DATA_CONTROLLER";

    public function indexAction() {
        $type = $this->_getParam("type");
        $subType = $this->_getParam("subtype");
        $name = $this->_getParam("name");
        switch ($type) {
            case "main-menu":
                MainMenuRenderer::generateXML($this->getRequest());
                break;
            case "window-description":
                $model = XMLParser::getWindowDescription($name);
                if (AuthManager::checkAccess($model->security, $this->getRequest())) {
                    WindowRenderer::generateXML($name);
                }
                break;
            case "window-content":
                $model = XMLParser::getWindowDescription($name);
                if (AuthManager::checkAccess($model->security, $this->getRequest())) {
                    $window = ApplicationManager::getCachedValue(ApplicationManager::$WINDOW_CONTENT, $name);
                    echo STParser::parse($window, $this->_getAllParams());
                }
                break;
            case "model":
                $model = XMLParser::getModel($name);
                if (AuthManager::checkAccess($model->security, $this->getRequest())) {
                    $this->renderObject($model);
                }
                break;
            case "unique":
                $this->checkUnique($name, $this->_getParam("idname"), $this->_getParam("idvalue"));
                break;
            default:
                Logger::warning(self::$log_type, "Unknown type: " . $type);
        }
    }

    public function processorAction() {
        $name = $this->_getParam("name");
        $type = $this->_getParam("type");
        Logger::notice(self::$log_type, "Processing model: " . $name);
        $model = ApplicationManager::getCachedValue(ApplicationManager::$DATA_MODEL_SQL, $name);
        $xmlModel = simplexml_load_string($model);
        $connectorObject = $this->getConnectorObject($xmlModel);
        $requestId = $this->_getParam("ids", null);
        $idName = $connectorObject->getIdName($xmlModel);
        $values = $this->getValues($type, $xmlModel, $connectorObject, $requestId);
        $state = $this->_getParam($requestId . "_!nativeeditor_status");
        $idValue = $connectorObject->$state($xmlModel, $values);
        header('Content-type: text/xml');
        echo "<?xml version=\"1.0\"?>";
        echo "<data><action type=\"$state\" sid=\"" . $idValue . "\" tid=\"id\" /></data>";
    }

    private function checkUnique($queryName, $idName, $idValue) {
        $name = $this->_getParam("cname");
        $value = $this->_getParam("cvalue");
        header('Content-type: text/xml');
        echo "<?xml version=\"1.0\"?>";
        $result = count(DBManager::getData($queryName, array("$idName" => $idValue, "$name" => $value)));
        echo "<data><action type=\"uniqueTest\" result=\"$result\" /></data>";
    }

    private function renderObject($xml) {
        $instance = $this->getConnectorObject($xml);
        $instance->parseRequest($_GET, $xml);
    }

    private function getConnectorObject($xml) {
        switch ($xml->type) {
            case "sql":
                $className = $this->getSQLClassName($this->_getParam("subtype"));
                break;
            case "cc":
                $className = $xml->cc->className->__toString();
                break;
            default:
                Logger::warning(self::$log_type, "Unknown model type: " . $xml->type);
        }
        require_once("connectors/" . $className . ".php");
        return new $className;
    }

    private function getSQLClassName($subtype) {
        switch ($subtype) {
            case "grid":
                return "SQLGridConnector";
            case "form":
                return "SQLFormConnector";
            default:
                Logger::warning(self::$log_type, "Unknown modelsub type: " . $subtype);
        }
    }

    private function getValues($type, $xml, $connectorObject, $id) {
        $values = array();
        $idName = $connectorObject->getIdName($xml);
        switch ($type) {
            case "grid":
                $values["$idName"] = $id;
                $columns = $connectorObject->getColumnsNames($xml);
                for ($c = 0; $c < count($columns); $c++) {
                    $values[$columns[$c]] = $this->_getParam($id . "_c" . $c, null);
                }
                return $values;
                break;
            case "form":
                if ($this->_getParam($id . "_" . $idName) != 0) {
                    $values["$idName"] = $this->_getParam($idName);
                    $this->_setParam($id . "_!nativeeditor_status", "updated");
                } else {
                    unset($_POST[$id . "_" . $idName]);
                }
                foreach ($_POST as $key => $value) {
                    if ($key != "ids" && $key != $id . "_!nativeeditor_status") {
                        $key = substr($key, strrpos($key, "_") + 1);
                        $values["$key"] = $value;
                    }
                }
                return $values;
                break;
            default:
                Logger::warning(self::$log_type, "Unknown processor type: " . $type);
        }
    }

}
?>
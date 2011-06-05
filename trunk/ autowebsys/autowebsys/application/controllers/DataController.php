<?php

require_once('core/ApplicationManager.php');
require_once('core/DBManager.php');
require_once('core/Logger.php');
require_once('core/renderers/MainMenuRenderer.php');
require_once('core/renderers/WindowRenderer.php');

/**
 * Kontroler dostępu do danych(okna, modele itp). Klasa wymaga dosyć pilnej
 * uwagi i reorganizacji kodu
 * @author Tomasz 'lobo' Kopacki
 * @email tomasz@kopacki.eu
 */
class DataController extends Zend_Controller_Action {

    private static $log_type = "DATA_CONTROLLER";

    /**
     * Funkcja sterująca. Sterowania odbywa się na podstawie parametru żądania.
     * Można by to przestawić - każdy typ żądania powinien mieć swoją akcję.
     * Operacja dosyć skomplikowana ze względu na zapisane na sztywno URL'e
     * w wielu miejscach kodu. Zrobić też coś ze sprawdzaniem uprawnień, najlepiej
     * jakiś plugin do tego.
     */
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

    /**
     * Obsługuje aktualizację danych przysłanych od klienta. Niby funkcja krótka
     * ale wygląda beznadziejnie. Na pewno da się to poprawić. Np. wywalić
     * echo i header, bleh...
     */
    public function processorAction() {
        $name = $this->_getParam("name");
        $type = $this->_getParam("type");
        Logger::notice(self::$log_type, "Processing model: " . $name);
        $model = ApplicationManager::getCachedValue(ApplicationManager::$DATA_MODEL_SQL, $name);
        $xmlModel = simplexml_load_string($model);
        $connectorObject = $this->getConnectorObject($xmlModel);
        $requestId = $this->_getParam("ids", null);
        $values = $this->getValues($type, $xmlModel, $connectorObject, $requestId);
        Logger::notice(self::$log_type, "Received values: " . implode(", ", $values));
        $state = $this->_getParam($requestId . "_!nativeeditor_status");
        $idValue = $connectorObject->$state($xmlModel, $values);
        header('Content-type: text/xml');
        echo "<?xml version=\"1.0\"?>";
        echo "<data><action type=\"$state\" sid=\"" . $idValue . "\" tid=\"id\" /></data>";
    }

    /**
     * Funkcja sprawdzjąca unikatowość zadanej wartości. Wartości pobierane
     * są z request'a, trochę to krzywe, można pomyśleć jak to zrobić lepiej.
     * Czy ta funkcja na pewno powinno być tutaj ?
     * @param string $queryName nazwa zapytania do sprawdzenia unikalności
     * @param string $idName nazwa atrybutu klucza głównego
     * @param string $idValue wartość klucza głównego
     */
    private function checkUnique($queryName, $idName, $idValue) {
        $name = $this->_getParam("cname");
        $value = $this->_getParam("cvalue");
        header('Content-type: text/xml');
        echo "<?xml version=\"1.0\"?>";
        $result = count(DBManager::getData($queryName, array("$idName" => $idValue, "$name" => $value)));
        echo "<data><action type=\"uniqueTest\" result=\"$result\" /></data>";
    }

    /**
     * Funkcja, na podstawie podanego w parametrze modelu, tworzy connector
     * danych i wypluwa dane w postaci XML
     * @param SimpleXML $model model danych w postaci SimpleXML
     */
    private function renderObject($model) {
        $instance = $this->getConnectorObject($model);
        $instance->parseRequest($this->_getAllParams(), $model);
    }

    /**
     * Tworzy connector danych na podstawie podanego modelu. Ten switch wygląda
     * brzydko, trzeba się go jakoś pozbyć...
     * @param SimpleXML $model model danych w postaci SimpleXML
     * @return className
     */
    private function getConnectorObject($model) {
        switch ($model->type) {
            case "sql":
                $className = $this->getSQLClassName($this->_getParam("subtype"));
                break;
            case "cc":
                $className = $model->cc->className->__toString();
                break;
            default:
                Logger::warning(self::$log_type, "Unknown model type: " . $model->type);
        }
        require_once("connectors/" . $className . ".php");
        return new $className;
    }

    /**
     * Podaje nazwę connectora typu SQL - ta funkcja to jakaś masakra, przecież
     * da się to zapisać w jednej linijce albo w ogóle wywalić
     * @param string $subtype typ żądanego connectora
     * @return string nazwa klasy connectora
     */
    private function getSQLClassName($subtype) {
        switch ($subtype) {
            case "grid":
                return "SQLGridConnector";
            case "form":
                return "SQLFormConnector";
            case "combo":
                return "SQLComboConnector";
            case "tree":
                return "SQLTreeConnector";
            default:
                Logger::warning(self::$log_type, "Unknown modelsub type: " . $subtype);
        }
    }

    /**
     * O ja pier* !
     * @param <type> $type
     * @param <type> $xml
     * @param <type> $connectorObject
     * @param <type> $id
     * @return <type>
     */
    private function getValues($type, $xml, $connectorObject, $id) {
        $values = array();
        $idName = $connectorObject->getIdName($xml);
        switch ($type) {
            case "grid":
                $values["$idName"] = trim($id);
                $columns = $connectorObject->getColumnsNames($xml);
                for ($c = 0; $c < count($columns); $c++) {
                    $values[$columns[$c]] = trim($this->_getParam($id . "_c" . $c, null));
                }
                return $values;
                break;
            case "tree":
                $values["$idName"] = trim($id);
                $column = $connectorObject->getColumnName($xml);
                $parent = $connectorObject->getParentIdName($xml);
                $values[$column] = trim($this->_getParam($id . "_tr" . "_text", null));
                $values[$parent] = trim($this->_getParam($id . "_tr" . "_pid", null));
                return $values;
                break;
            case "form":
                if ($this->_getParam($id . "_" . $idName) != 0) {
                    $values["$idName"] = trim($this->_getParam($id . "_" . $idName));
                    $this->_setParam($id . "_!nativeeditor_status", "updated");
                } else {
                    unset($_POST[$id . "_" . $idName]);
                }
                foreach ($_POST as $key => $value) {
                    if ($key != "ids" && $key != $id . "_!nativeeditor_status") {
                        $key = substr($key, strpos($key, "_") + 1);
                        $values["$key"] = trim($value);
                        if($values["$key"] == "NULL") {
                            $values["$key"] = null;
                        }
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
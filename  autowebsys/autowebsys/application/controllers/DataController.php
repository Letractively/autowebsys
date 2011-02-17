<?php

require_once('core/ApplicationManager.php');
require_once('core/DBManager.php');
require_once('core/Logger.php');
require_once('connectors/grid_connector.php');
require_once('connectors/form_connector.php');
require_once('connectors/db_pdo.php');

class DataController extends Zend_Controller_Action {

    private static $log_type = "DATA_CONTROLLER";

    public function indexAction() {
        $type = $this->_getParam("type");
        $subType = $this->_getParam("subtype");
        $name = $this->_getParam("name");

        switch ($type) {
            case "main-menu":
                header('Content-type: text/xml');
                echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
                $menu = ApplicationManager::getCachedValue(ApplicationManager::INTERFACE_MAINMENU);
                echo STParser::parse($menu);
                break;
            case "window-description":
                header('Content-type: text/xml');
                echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
                $window = ApplicationManager::getCachedValue(ApplicationManager::WINDOW_DESCRIPTION, $name);
                echo STParser::parse($window);
                break;
            case "window-content":
                $window = ApplicationManager::getCachedValue(ApplicationManager::WINDOW_CONTENT, $name);
                echo STParser::parse($window, $this->_getAllParams());
                break;
            case "model":
                $conn = $this->getConnection($subType);
                $model = ApplicationManager::getCachedValue(ApplicationManager::DATA_MODEL_SQL, $name);
                $xmlModel = simplexml_load_string($model);
                $sql = ApplicationManager::getCachedValue(ApplicationManager::DB_QUERY, $xmlModel->sql->select->__toString());
                $conn->enable_log(Logger::getLogPath() . "dhtmlx.log");
                $conn->render_sql($sql, $xmlModel->sql->id->__toString(), $xmlModel->sql->columns->__toString(), $xmlModel->sql->hidden_columns->__toString(), $xmlModel->sql->parent_id->__toString());
                break;
            case "delete":
                $model = ApplicationManager::getCachedValue(ApplicationManager::DATA_MODEL_SQL, $name);
                $xmlModel = simplexml_load_string($model);
                $id = $this->_getParam("id", 0);
                $idName = $xmlModel->sql->id;
                $values = array("$idName" => $id);
                DBManager::execute($xmlModel->sql->delete, $values);
                break;
            default:
                Logger::warning(self::$log_type, "Unknown type: " . $type);
        }
    }

    public function processorAction() {
        $name = $this->_getParam("name");
        $type = $this->_getParam("type");
        $idValue = $this->_getParam("gr_id", null);
        Logger::notice(self::$log_type, "Processing model: " . $name);
        $model = ApplicationManager::getCachedValue(ApplicationManager::DATA_MODEL_SQL, $name);
        $xmlModel = simplexml_load_string($model);
        $values = $this->getValue($type, $xmlModel, $idValue);
        $state = "";
        if($idValue == 0) {
            $state = "inserted";
        } else {
            if ($this->_getParam("!nativeeditor_status", "updated") == "inserted") {
                $state = "updated";
            } else {
                $state = $this->_getParam("!nativeeditor_status", "updated");
            }
        }
        switch ($state) {
            case "inserted":
                Logger::notice(self::$log_type, "Persisting data: " . implode(", ", $values) . " with sql: " . $xmlModel->sql->insert);
                $idValue = DBManager::insert($xmlModel->sql->insert, $values);
                break;
            case "updated":
                Logger::notice(self::$log_type, "Persisting data: " . implode(", ", $values) . " with sql: " . $xmlModel->sql->update);
                DBManager::execute($xmlModel->sql->update, $values);
                break;
            case "deleted":
                Logger::notice(self::$log_type, "Persisting data: " . implode(", ", $values) . " with sql: " . $xmlModel->sql->delete);
                DBManager::execute($xmlModel->sql->delete, $values);
                break;
            default:
                Logger::warning(self::$log_type, "Unknown state: " . $state);
        }
        header('Content-type: text/xml');
        echo "<?xml version=\"1.0\"?>";
        echo "<data><action type=\"$state\" sid=\"$idValue\" tid=\"id\" /></data>";
    }

    private function getConnection($subType) {
        switch ($subType) {
            case "grid":
                return new GridConnector(DBManager::getConnector(), "PDO");
                break;
            case "form":
                return new FormConnector(DBManager::getConnector(), "PDO");
                break;
            default:
                Logger::warning(self::$log_type, "Unknown subtype: " . $subType);
        }
    }

    private function getValue($type, $xml, $id) {
        $values = array();
        $idName = $xml->sql->id;
        switch ($type) {
            case "grid":
                $values["$idName"] = $id;
                $columns = explode(",", $xml->sql->columns);
                for ($c = 0; $c < count($columns); $c++) {
                    $values[$columns[$c]] = $this->_getParam("c" . $c, null);
                }
                return $values;
                break;
            case "form":
                if ($id != 0) {
                    $values["$idName"] = $id;
                }
                foreach ($_GET as $key => $value) {
                    if ($key != "gr_id" && $key != "!nativeeditor_status") {
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
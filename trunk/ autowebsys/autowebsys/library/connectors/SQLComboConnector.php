<?php

require_once("core/DBManager.php");
require_once("core/connectors/DataComboConnector.php");

class SQLComboConnector extends DataComboConnector {

    public function getColumnName($xml) {
        return $xml->sql->columns->__toString();
    }

    public function getData() {
        $queryName = $this->model->sql->select->__toString();
        Logger::notice("SQLComboConnector", "Executing query '$queryName'");
        return DBManager::getData($queryName);
    }

    public function getIdName($xml) {
        return $xml->sql->id->__toString();
    }

}
?>

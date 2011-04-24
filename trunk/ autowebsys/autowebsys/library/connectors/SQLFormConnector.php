<?php

require_once("core/ApplicationManager.php");
require_once("core/DBManager.php");
require_once("core/connectors/DataFormConnector.php");

class SQLFormConnector extends DataFormConnector {

    public function getIdName($xml) {
        return $xml->sql->id->__toString();
    }

    public function inserted($xml, $data) {
        $queryName = $xml->sql->insert->__toString();
        $sequenceName = $xml->sql->sequence->__toString();
        return DBManager::insert($queryName, $sequenceName, $data);
    }

    public function updated($xml, $data) {
        $queryName = $xml->sql->update->__toString();
        $idName = $this->getIdName($xml);
        DBManager::execute($queryName, $data);
        return $data["$idName"];
    }

    public function  deleted($xml, $data) {
        
    }

    public function getData() {
        $queryName = $this->model->sql->select->__toString();
        $idName = $this->getIdName($this->model);
        return DbManager::getData($queryName, array("$idName" => $this->idValue));
    }

}
?>

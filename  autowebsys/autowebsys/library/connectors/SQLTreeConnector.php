<?php

require_once("core/connectors/DataTreeConnector.php");
require_once("core/structures/Tree.php");

class SQLTreeConnector extends DataTreeConnector {

    public function getData() {
        $queryName = $this->model->sql->select->__toString();
        return DbManager::getData($queryName);
    }

    public function getIdName($xml) {
        return $xml->sql->id->__toString();
    }

    public function getColumnName($xml) {
        return $xml->sql->label->__toString();
    }

    public function getLftName($xml) {
        return $xml->sql->lft->__toString();
    }

    public function getParentIdName($xml) {
        return $xml->sql->parent_id->__toString();
    }

    public function getRgtName($xml) {
        return $xml->sql->rgt->__toString();
    }

    public function inserted($xml, $data) {

    }

    public function updated($xml, $data) {
        $queryName = $xml->sql->update->__toString();
        $idName = $this->getIdName($xml);
        DBManager::execute($queryName, $data);
        $tree = new Tree();
        $tree->rebuildWholeTree($xml);
        return $data["$idName"];
    }

    public function deleted($xml, $data) {
        $queryName = $xml->sql->delete->__toString();
        $idName = $this->getIdName($xml);
        DBManager::execute($queryName, array("$idName" => $data["$idName"]));
        $tree = new Tree();
        $tree->rebuildWholeTree($xml);
        return $data["$idName"];
    }

}
?>

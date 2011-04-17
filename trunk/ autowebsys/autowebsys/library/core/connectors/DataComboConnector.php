<?php

require_once("core/connectors/DataConnector.php");

abstract class DataComboConnector extends DataConnector {

    protected $model;
    protected $idName;
    protected $columnName;

    abstract public function getIdName($xml);

    abstract public function getColumnName($xml);

    abstract public function getData();

    public function parseRequest($parameters, $model) {
        $this->model = $model;
        $this->idName = $this->getIdName($this->model);
        $this->columnName = $this->getColumnName($this->model);
        $data = $this->getData();
        $this->generateXML($data);
    }

    protected function generateXML($data) {
        header('Content-type: text/xml');
        $out = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $out .= "<data>";
        $idName = $this->idName;
        $columnName = $this->columnName;
        foreach ($data as $row) {
            $id = $row->$idName;
            $value = $row->$columnName;
            $out .= "<item value='$id' label='$value' />";
        }
        $out .= "</data>";
        echo $out;
    }

    public function inserted($xml, $data) {

    }

    public function updated($xml, $data) {

    }

    public function deleted($xml, $data) {

    }

}
?>

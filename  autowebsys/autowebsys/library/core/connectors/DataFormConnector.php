<?php

require_once("core/connectors/DataConnector.php");

abstract class DataFormConnector extends DataConnector {

    protected $model;
    protected $idName;
    protected $idValue;

    abstract public function getIdName($xml);

    abstract public function getData();

    abstract public function inserted($xml, $data);

    abstract public function updated($xml, $data);

    abstract public function deleted($xml, $data);

    public function parseRequest($parameters, $model) {
        $this->model = $model;
        $this->idValue = $parameters["id"];
        $data = $this->getData();
        $this->generateXML($data);
    }

    protected function generateXML($data) {
        header('Content-type: text/xml');
        $out = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $out .= "<data>";
        if (count($data) == 1) {
            foreach ($data[0] as $key => $value) {
                if($value != "") {
                    $out .= "<$key>$value</$key>";
                }
            }
        }
        $out .= "</data>";
        echo $out;
    }

}
?>

<?php

require_once("core/connectors/DataConnector.php");
require_once("core/structures/Stack.php");

abstract class DataTreeConnector extends DataConnector {

    protected $model;
    protected $idName;
    protected $parentIdName;
    protected $lftName;
    protected $rgtName;
    protected $columnName;

    abstract public function getIdName($xml);

    abstract public function getParentIdName($xml);

    abstract public function getLftName($xml);

    abstract public function getRgtName($xml);

    abstract public function getColumnName($xml);

    abstract public function getData();

    public function parseRequest($parameters, $model) {
        $this->model = $model;
        $this->idName = $this->getIdName($this->model);
        $this->parentIdName = $this->getParentIdName($this->model);
        $this->lftName = $this->getLftName($this->model);
        $this->rgtName = $this->getRgtName($this->model);
        $this->columnName = $this->getColumnName($this->model);
        $data = $this->getData();
        $this->generateXML($data);
    }

    protected function generateXML($data) {
        $idName = $this->idName;
        $columnName = $this->columnName;
        $id = $data[0]->$idName;
        $stack = new Stack();
        header('Content-type: text/xml');
        $out = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $out .= "<tree id=\"0\">";
        for ($i = 0; $i < count($data); $i++) {
            $node = $data[$i];
            $id = $node->$idName;
            $label = $node->$columnName;
            while (!$stack->isEmpty() && $stack->whatsOnTop() < $node->lft) {
                $lower = $stack->pop();
                $out .= "</item>";
            }
            $out .= "<item id=\"$id\" text=\"$label\">";
            $stack->push($node->rgt);
        }
        while (!$stack->isEmpty()) {
            $lower = $stack->pop();
            $out .= "</item>";
        }
        $out .= "</tree>";
        echo $out;
    }

    abstract public function inserted($xml, $data);

    abstract public function updated($xml, $data);

    abstract public function deleted($xml, $data);
}
?>

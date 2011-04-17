<?php

require_once("core/Logger.php");
require_once("core/connectors/DataConnector.php");

abstract class DataGridConnector extends DataConnector {

    protected $sortColumn = null;
    protected $sortOrder = null;
    protected $filters;
    protected $posStart = 0;
    protected $count = 100;
    protected $model;

    abstract protected function getData($parameters);

    abstract public function getIdName($xml);

    abstract public function getColumnsNames($xml);

    public function inserted($xml, $data) {

    }

    public function updated($xml, $data) {

    }

    public function deleted($xml, $data) {

    }

    abstract public function getTotalCount($query);

    public function parseRequest($parameters, $model) {
        if (isset($parameters["dhx_sort"])) {
            $this->getSortColumn($parameters["dhx_sort"]);
        }
        $this->filters = (isset($parameters["dhx_filter"]) ? $parameters["dhx_filter"] : array());
        $this->posStart = (isset($parameters["posStart"]) ? $parameters["posStart"] : 0);
        $this->count = (isset($parameters["count"]) ? $parameters["count"] : 1000);
        $this->model = $model;
        $start = time();
        $data = $this->getData($parameters);
        $end = time() - $start;
        $modelName = $this->model->name;
        Logger::notice("DataGridConnector", "Data from $modelName loaded in $end ms");
        $this->generateXML($data);
    }

    protected function generateXML($data) {
        $start = time();
        header('Content-type: text/xml');
        $out = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $out .= "<rows pos=\"$this->posStart\" total_count=\"$this->totalCount\">";
        foreach ($data as $row) {
            $idName = $this->getIdName($this->model);
            $id = $row->$idName;
            $out .= "<row id='$id'>";
            foreach ($row as $key => $item) {
                if ($key != $this->getIdName($this->model)) {
                    $out .= "<cell>$item</cell>";
                }
            }
            $out .= "</row>";
        }
        $out .= "</rows>";
        $end = time() - $start;
        $modelName = $this->model->name;
        Logger::notice("DataGridConnector", "XML from $modelName generated in $end ms");
        echo $out;
    }

    private function getSortColumn($sort) {
        foreach ($sort as $key => $value) {
            $this->sortColumn = $key;
            $this->sortOrder = $value == "asc" ? "ASC" : "DESC";
        }
    }

}
?>

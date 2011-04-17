<?php

require_once("core/Logger.php");
require_once("core/ApplicationManager.php");
require_once("core/DBManager.php");
require_once("core/connectors/DataGridConnector.php");

class SQLGridConnector extends DataGridConnector {

    public function deleted($xml, $data) {
        $queryName = $xml->sql->delete->__toString();
        $idName = $this->getIdName($xml);
        DBManager::execute($queryName, array("$idName" => $data["$idName"]));
        return $data["$idName"];
    }

    public function updated($xml, $data) {
        $queryName = $xml->sql->update->__toString();
        $idName = $this->getIdName($xml);
        DBManager::execute($queryName, $data);
        return $data["$idName"];
    }

    public function getTotalCount($query) {
        $query = "SELECT count(*) as i FROM ($query) as q;";
        $row = DBManager::fetchRow($query);
        Logger::notice("SQLGridConnector", "Counting rows: $query - $row->i");
        return $row->i;
    }

    public function getIdName($xml) {
        return $xml->sql->id->__toString();
    }

    public function getColumnsNames($xml) {
        return explode(",", $xml->sql->columns);
    }

    protected function getData($parameters) {
        $query = ApplicationManager::getCachedValue(ApplicationManager::$DB_QUERY, $this->model->sql->select->__toString());
        $query = $this->addFilters($query);
        $query = $this->checkForParameters($query, $parameters);
        $this->totalCount = $this->getTotalCount($query);
        $query = $this->addSortOrder($query);
        $query = $this->addLimit($query);
        Logger::notice("SQLGridConnector", "Executing query '$query'");
        return DBManager::fetchAll($query);
    }

    private function addFilters($query) {
        $columns = explode(",", $this->model->sql->columns->__toString());
        $query = "SELECT * FROM ($query) as q";
        if (count($this->filters) > 0) {
            $query .= " WHERE ";
            foreach ($this->filters as $key => $value) {
                $query .= "q.$columns[$key] LIKE '$value%' AND ";
            }
            $query = substr($query, 0, -4);
        }
        return $query;
    }

    private function addSortOrder($query) {
        if (isset($this->sortColumn) && isset($this->sortOrder)) {
            $columns = explode(",", $this->model->sql->columns->__toString());
            $columnName = $columns[$this->sortColumn];
            return $query . " ORDER BY q.$columnName $this->sortOrder";
        }
        return $query;
    }

    private function addLimit($query) {
        return $query . " LIMIT $this->count OFFSET $this->posStart";
    }

}
?>

<?php

require_once("core/connectors/DataGridConnector.php");

class MyConnector extends DataGridConnector {

    public function getIdName($xml) {
        return "id";
    }

    public function getColumnsNames($xml) {
        return array("extension", "username", "balance");
    }

    public function updated($xml, $data) {

    }

    public function deleted($xml, $data) {

    }

    protected function getData() {
        $data = array(
            array("id" => 1, "1" => 1000, "2" => "Michał", "3" => 49.380),
            array("id" => 2, "1" => 1001, "2" => "Tomek K.", "3" => 123.000),
            array("id" => 3, "1" => 1002, "2" => "Marek", "3" => 3.000),
        );
        $data = $this->filterData($data);
        $data = $this->sortData($data);
        return $data;
    }

    private function sortData($data) {
        usort($data, array($this, 'compare'));
        return $data;
    }

    private function compare($a, $b) {
        if ($this->sortOrder == "asc") {
            return strnatcmp($a[$this->sortColumn + 1], $b[$this->sortColumn + 1]);
        } else {
            return strnatcmp($b[$this->sortColumn + 1], $a[$this->sortColumn + 1]);
        }
    }

    private function filterData($data) {
        $flag = true;
        $newData = array();
        foreach ($data as $row) {
            $flag = true;
            $newRow = array();
            foreach ($row as $key => $value) {
                $newRow[$key] = $value;
                if ($key != $this->getIdName() && isset($this->filters[$key - 1])) {
                    if (is_string($value) && $this->filters[$key - 1] != "" && !stristr($value, $this->filters[$key - 1])) {
                        $flag = false;
                    }
                    if (is_numeric($value) && is_numeric($this->filters[$key - 1]) && $value != $this->filters[$key - 1]) {
                        $flag = false;
                    }
                }
            }
            if ($flag == true) {
                $newData[] = $newRow;
            }
        }
        return $newData;
    }

}
?>

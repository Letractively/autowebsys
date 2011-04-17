<?php

abstract class DataConnector {

    abstract protected function generateXML($data);

    abstract public function inserted($xml, $data);

    abstract public function updated($xml, $data);

    abstract public function deleted($xml, $data);

    public function checkForParameters($query, $parameters) {
        $i = 0;
        while ($i = strpos($query, ":")) {
            $endOfParameter = strpos($query, " ", $i);
            $parameter = substr($query, $i, $endOfParameter - $i - 1);
            $cleanParameter = substr($parameter, 1);
            $query = str_replace($parameter, $parameters[$cleanParameter], $query);
        }
        return $query;
    }

}
?>

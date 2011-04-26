<?php

abstract class DataConnector {

    abstract protected function generateXML($data);

    public function checkForParameters($query, $parameters) {
        $i = 0;
        while ($i = strpos($query, ":")) {
            $f_possibleEndOfParameter = strpos($query, " ", $i);
            $s_possibleEndOfParameter = strpos($query, ")", $i);
            $endOfParameter = min($f_possibleEndOfParameter, $s_possibleEndOfParameter);
            $parameter = substr($query, $i, $endOfParameter - $i);
            $cleanParameter = substr($parameter, 1);
            $query = str_replace($parameter, $parameters[$cleanParameter], $query);
        }
        return $query;
    }

}
?>

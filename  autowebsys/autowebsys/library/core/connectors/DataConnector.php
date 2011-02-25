<?php

abstract class DataConnector {

    abstract protected function generateXML($data);

    abstract public function inserted($xml, $data);

    abstract public function updated($xml, $data);

    abstract public function deleted($xml, $data);
}
?>

<?php

class Tree {

    public $xml;
    public $name;
    public $type;
    public $uid;
    public $taskbarName;
    public $processorName;

    function __construct($xml) {
        $this->xml = simplexml_load_string($xml);
        $uid = WindowRenderer::getUID();
        $this->uid = $uid;
        $this->name = "tree" . $uid;
        $this->type = $this->xml->name;
        $this->taskbarName = "taskbar" . $uid;
        $this->processorName = "processor" . $uid;
    }

}
?>

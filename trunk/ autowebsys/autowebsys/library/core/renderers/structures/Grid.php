<?php

class Grid {
    public $xml;
    public $type;
    public $name;
    public $uid;
    public $taskbarName;
    public $processorName;
    
    function __construct($xml) {
        $this->xml = simplexml_load_string($xml);
        $uid = WindowRenderer::getUID();
        $this->uid = $uid;
        $this->type = $this->xml->name;
        $this->name = $this->type . $uid;
        $this->taskbarName = "taskbar" . $uid;
        $this->processorName = "processor" . $uid;
    }

}
?>

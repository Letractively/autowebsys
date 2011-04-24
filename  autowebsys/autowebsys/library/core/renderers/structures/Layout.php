<?php

class Layout {

    public $name;
    public $type;
    public $cells;
    public $xml;

    function __construct($xml) {
        $this->name = "l" . WindowRenderer::getUID();
        $this->xml = simplexml_load_string($xml);
        $this->type = $this->xml->type->__toString();
        $cells = array();
        foreach ($this->xml->cells->children() as $cell) {
            $this->cells[$cell->name->__toString()] = $cell;
        }
    }

}
?>

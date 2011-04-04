<?php

class Form {
    public $xml;
    public $templateName;
    public $name;
    public $type;
    public $uid;
    public $processorName;
    public $template;
    
    function __construct($xml) {
        $this->xml = simplexml_load_string($xml);
        $this->template = simplexml_load_string(ApplicationManager::getCachedValue(ApplicationManager::$DATA_TEMPLATE, $this->xml->template));
        $uid = WindowRenderer::getUID();
        $this->uid = $uid;
        $this->type = $this->xml->name;
        $this->name = $this->type . $uid;
        $this->formName = "form" . $uid;
        $this->processorName = "processor" . $uid;
    }

}
?>

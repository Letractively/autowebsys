<?php

require_once("core/renderers/WindowRenderer.php");
require_once("core/XMLParser.php");

abstract class Structure {

    protected $uid;
    protected $name;
    protected $structureModel;
    private $jsOpened;
    private $rendered;

    public function Structure($modelName) {
        $this->structureModel = $this->loadModel($modelName);
        $this->generateName();
        $this->jsOpened = false;
        $this->rendered = "";
    }

    private function loadModel($modelName) {
        return XMLParser::getModel($modelName);
    }

    private function generateName() {
        $this->uid = WindowRenderer::getUID();
        $this->name = "g_" . $this->uid;
    }
    
    protected function createDiv($name) {
        $this->rendered .= "<div id=\"$name\"></div>";
    }
    
    protected function createJSOpen() {
        $this->rendered .= "<script type=\"text/javascript\">";
    }
    
    protected function createJSClose() {
        $this->rendered .= "</script>";
    }


    protected function addJavaScriptLine($line) {
        if(!$this->jsOpened) {
            $this->rendered .= $this->createJSOpen();
            $this->jsOpened = true;
        }
        $this->rendered .= $line;
    }
    
    public function render() {
        $this->renderObject();
         if($this->jsOpened) {
             $this->rendered .= $this->createJSClose();
         }
         return $this->rendered;
    }
    
    abstract public function renderObject();
}

?>

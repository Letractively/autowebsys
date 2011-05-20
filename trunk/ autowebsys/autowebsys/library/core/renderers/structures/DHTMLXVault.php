<?php

require_once("core/renderers/structures/Structure.php");

class DHTMLXVault extends Structure {

    private $controllerModel;
    private $handlers = "/custom/index";
    private $info = "/function/info";
    private $id = "/function/id";
    private $upload = "/function/upload";

    public function DHTMLXVault($modelName) {
        parent::Structure($modelName);
        $this->loadController($this->structureModel->controller);
        $this->initURLs($this->controllerModel->name, $this->structureModel->name);
    }

    private function loadController($controllerName) {
        $this->controllerModel = XMLParser::xmlStringAsObject(ApplicationManager::getCachedValue(ApplicationManager::$CUSTOM_CONTROLLER, $controllerName));
    }

    private function initURLs($controllerName, $vaultName) {
        $this->handlers = $this->handlers . "/name/" . $controllerName;
        $this->info = $this->handlers . $this->info . "/model/" . $vaultName;
        $this->id = $this->handlers . $this->id . "/model/" . $vaultName;
        $this->upload = $this->handlers . $this->upload . "/model/" . $vaultName;
    }

    public function renderObject() {
        $this->createDiv($this->name);
        $this->addJavaScriptLine("var $this->name = new dhtmlXVaultObject();");
        $this->addJavaScriptLine("$this->name.setImagePath('/dhtmlx/imgs/');");
        $this->addJavaScriptLine("$this->name.setServerHandlers(");
        $this->addJavaScriptLine("'$this->upload',");
        $this->addJavaScriptLine("'$this->info',");
        $this->addJavaScriptLine("'$this->id'");
        $this->addJavaScriptLine(");");
        $this->addJSFromModel();
        $this->addOnUploadComplete();
        $this->addJavaScriptLine("$this->name.create('$this->name');");
    }
    
    private function addJSFromModel() {
        if(isset($this->structureModel->js)) {
            foreach ($this->structureModel->js->children() as $jsTag) {
                $parsedValue = STParser::parse($jsTag->__toString());
                $tagName = $jsTag->getName();
                $this->addJavaScriptLine("$this->name.$tagName($parsedValue);");
            }
        }
    }
    
    private function addOnUploadComplete() {
        if(isset($this->structureModel->onUploadComplete)) {
            $onUploaded = $this->structureModel->onUploadComplete->__toString();
            $this->addJavaScriptLine("$this->name.onUploadComplete = $onUploaded;");
        }
    }

    public function getControllerModel() {
        return $this->controllerModel;
    }
    
    public function getStructureModel() {
        return $this->structureModel;
    }

}

?>

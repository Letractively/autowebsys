<?php

abstract class AbstractCustomController {

    protected $parentController;

    public function AbstractCustomController() {
        
    }

    public function setController(Zend_Controller_Action $parentController) {
        $this->parentController = $parentController;
    }

    abstract public function handleRequest();
}
?>

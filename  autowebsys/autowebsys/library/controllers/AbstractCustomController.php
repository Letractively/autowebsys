<?php

/**
 * Klasa abstrakcyjna dla kontrolerów użytkownika. Dorzuca tylko referencję
 * do CustomController, tak żeby mieć dostęp do parametrów i reszty tego
 * badziewia.
 * @author Tomasz 'lobo' Kopacki
 * @email tomasz@kopacki.eu
 */
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

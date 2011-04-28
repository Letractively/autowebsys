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
    protected $requestParameters;

    public function AbstractCustomController() {
        
    }

    public function setController(Zend_Controller_Action $parentController) {
        $this->requestParameters = $parentController;
    }
    
    public function setRequestParameters($parameters) {
	$this->requestParameters = $parameters;
    }    

    public function hasParameter($name) {
	return isset($this->requestParameters[$name]);
    }

    public function getParameter($name, $default = null) {
        if($this->hasParameter($name)) {
            return $this->requestParameters[$name];
        } else {
            return $default;
        }
    }

    abstract public function handleRequest();
}
?>

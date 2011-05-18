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
        $this->parentController = $parentController;
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

    public function getParameters() {
        return $this->requestParameters;
    }

    protected function setXmlHeader() {
        header('Content-type: text/xml');
    }

    protected function getXmlVersion() {
        return "<?xml version=\"1.0\"?>";
    }

    abstract public function handleRequest();

    /**
     * Metoda jest wywoływana przed handleRequest. Można ją przeciążać
     * w celu sprawdzania dostępnych parametrow itp. Każdy wyrzucony wyjątek
     * zotanie złapany i przekazany do logów a metoda handleRequest nie zostanie
     * wywołana.
     */
    public function init() {
    }
    
    /**
     * Metoda zwraca standardową odpowiedz typu XML
     * @param type $type typ zwracanej odpowiedzi
     * @param type $result najczęsciej wartość 0|1 - 0 OK, 1 źle
     * @param type $errors opis błędu
     */
    public function xmlResponse($type, $result, $errors = "") {
        $this->setXmlHeader();
        return $this->getXmlVersion() .
            "<data><action type=\"$type\" result=\"$result\" errors=\"$errors\" /></data>";
    }
}
?>

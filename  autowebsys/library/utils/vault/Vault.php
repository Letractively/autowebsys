<?php

abstract class Vault {
    const START = 0;
    const SUCCESS = -1;
    const INVALIDATE = -2;
    const ERROR = -3;

    protected $functionsArray;
    protected $sessionId;

    public function Vault($sessionId) {
        $this->sessionId = $sessionId;
        $this->functionsArray = array(
            Vault::SUCCESS => "endSession",
            Vault::INVALIDATE => "invalidateSession",
            Vault::ERROR => "errorSession",
        );
    }
    
    private function getSessionHandler() {
        return new Zend_Session_Namespace("file_upload_handler_" . $this->sessionId);
    }
    
    public function getSessionId() {
        return $this->sessionId;
    }
    
    public function setState($state) {
        $sessionHandler = $this->getSessionHandler();
        $sessionHandler->state = $state;
    }
    
    public function getState() {
        $sessionHandler = $this->getSessionHandler();
        return $sessionHandler->state;
    }
    
    public function destroySessionHandler() {
        $sessionHandler = $this->getSessionHandler();
        $sessionHandler->unsetAll();
    }

}

?>

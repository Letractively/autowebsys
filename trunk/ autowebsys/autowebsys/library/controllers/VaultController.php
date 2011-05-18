<?php

require_once('utils/vault/VaultInfo.php');
require_once('utils/vault/VaultId.php');
require_once('utils/vault/VaultUpload.php');
require_once('controllers/AbstractCustomController.php');
require_once('core/ApplicationManager.php');

class VaultController extends AbstractCustomController {

    private $vaultInfo;
    private $vaultId;
    private $vaultUpload;
    private $path;

    public function handleRequest() {
        $request = $this->getParameter("function", "info");
        return $this->$request();
    }

    private function info() {
        return $this->vaultInfo->getInfo();
    }
    
    private function id() {
        return $this->vaultId->getId();
    }
    
    private function upload() {
        $this->vaultUpload->setInputName($this->getParameter("userfile", ""));
        $this->vaultUpload->setFileName($_FILES[$this->vaultUpload->getInputName()]['name']);
        $this->vaultUpload->setTempLoc($_FILES[$this->vaultUpload->getInputName()]['tmp_name']);
        return $this->vaultUpload->getUpload($this->path);
    }

    public function init() {
        $this->vaultInfo = new VaultInfo($this->getParameter("sessionId", ""));
        $this->vaultId = new VaultId();
        $this->vaultUpload = new VaultUpload($this->getParameter("sessionId", ""));
        $this->path = ApplicationManager::getCachedValue(ApplicationManager::$PARAMETER, "tmpDir");
    }

}

?>

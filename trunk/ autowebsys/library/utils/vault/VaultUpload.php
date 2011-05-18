<?php

require_once('utils/vault/Vault.php');

class VaultUpload extends Vault {

    private $id;
    private $inputName;
    private $fileName;
    private $tempLoc;

    public function VaultUpload($sessionId) {
        parent::Vault($sessionId);
    }

    public function getUpload($path) {
        $target_path = $path . basename($this->fileName);
        if (move_uploaded_file($this->tempLoc, $target_path)) {
            $this->setState(Vault::SUCCESS);
        } else {
            $this->setState(Vault::ERROR);
        }
        return $_FILES[$this->inputName]['error'];
    }

    public function setInputName($inputName) {
        $this->inputName = $inputName;
    }
    
    public function getInputName() {
        return $this->inputName;
    }

    public function setFileName($fileName) {
        $this->fileName = $fileName;
    }

    public function setTempLoc($tempLoc) {
        $this->tempLoc = $tempLoc;
    }

}

?>

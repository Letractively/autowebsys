<?php

require_once('utils/vault/VaultInfo.php');
require_once('utils/vault/VaultId.php');
require_once('utils/vault/VaultUpload.php');
require_once('controllers/AbstractCustomController.php');
require_once('core/ApplicationManager.php');
require_once('core/renderers/structures/DHTMLXVault.php');

/**
 * Kontroller obsługi obiektu DHTMLXVault. Żadania są obsługiwane za pomocą
 * wywołań dynamicznych. Nazwy funkcji przychodzą w parametrze 'function'.
 * Do nazwy dodawany jest suffix 'Action', żeby uniknąć prób nieautoryzowanego
 * dostępu do pozostałych funkcji.
 * @param function nazwa funkcji, która ma obsłużyć żądanie
 * @param sessionId numer sesji uploadu tego pliku
 * @param model nazwa modelu tej instancji vault'a
 * @param userfile nazwa uploadowanego pliku
 */
class VaultController extends AbstractCustomController {

    private $vaultInfo;
    private $vaultId;
    private $vaultUpload;
    private $path;

    public function handleRequest() {
        $request = $this->getParameter("function", "id") . "Action";
        return $this->$request();
    }

    private function infoAction() {
        return $this->vaultInfo->getInfo();
    }
    
    private function idAction() {
        return $this->vaultId->getId();
    }
    
    private function uploadAction() {
        $this->vaultUpload->setInputName($this->getParameter("userfile", ""));
        $this->vaultUpload->setFileName($_FILES[$this->vaultUpload->getInputName()]['name']);
        $this->vaultUpload->setTempLoc($_FILES[$this->vaultUpload->getInputName()]['tmp_name']);
        return $this->vaultUpload->getUpload($this->path);
    }

    public function init() {
        $this->vaultInfo = new VaultInfo($this->getParameter("sessionId", ""));
        $this->vaultId = new VaultId();
        $this->vaultUpload = new VaultUpload($this->getParameter("sessionId", ""));
        $vaultModel = $this->getVaultModel($this->getParameter("model", ""));
        $this->path = $vaultModel->path->__toString();
    }
    
    private function getVaultModel($modelName) {
        $vault = new DHTMLXVault($modelName);
        return $vault->getStructureModel();
    }

}

?>

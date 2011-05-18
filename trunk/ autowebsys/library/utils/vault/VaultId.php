<?php

require_once('utils/vault/Vault.php');
require_once('core/renderers/WindowRenderer.php');

class VaultId extends Vault {

    public function VaultId() {
        parent::Vault(WindowRenderer::getUID());
    }

    public function getId() {
        $this->setState(Vault::START);
        return $this->getSessionId();
    }

}

?>

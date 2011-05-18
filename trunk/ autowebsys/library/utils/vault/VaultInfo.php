<?php

require_once('utils/vault/Vault.php');

class VaultInfo extends Vault {

    public function VaultInfo($sessionId) {
        parent::Vault($sessionId);
    }

    public function getInfo() {
        return $this->stateDispatcher();
    }

    private function stateDispatcher() {
        if (isset($this->functionsArray[$this->getState()])) {
            $functionName = $this->functionsArray[$this->getState()];
            return $this->$functionName();
        } else {
            return $this->status();
        }
    }

    private function endSession() {
        $this->setState(Vault::INVALIDATE);
        return 100;
    }

    private function invalidateSession() {
        $this->destroySessionHandler();
        return -1;
    }

    private function errorSession() {
        $maxPost = ini_get('post_max_size');
        $this->destroySessionHandler();
        return "error:-3:$maxPost:";
    }

    private function status() {
        $info = uploadprogress_get_info($this->sessionId);
        $bt = $info['bytes_total'];
        if ($bt < 1) {
            $percent = 0;
        } else {
            if (!$_SESSION['dhxvlt_max']) {
                $_SESSION['dhxvlt_max'] = true;
                $maxSizeM = ini_get('upload_max_filesize');
                $maxSize = $this->getBytes($maxSizeM);
                if ($maxSize < $bt) {
                    $this->setState(Vault::INVALIDATE);
                    return "error:-2:$bt:$maxSizeM:";
                }
            }
            Logger::info("VAULT_INFO", "Uploaded bytes: " . $info['bytes_uploaded']);
            $percent = round($info['bytes_uploaded'] / $bt * 100, 0);
        }
        return $percent;
    }

    private function getBytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }

}

?>

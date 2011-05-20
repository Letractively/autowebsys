<?php

require_once('tags/CustomTag.php');
require_once('core/renderers/structures/DHTMLXVault.php');

class TVault extends CustomTag {

    public function parseVault($params) {
        $vault = new DHTMLXVault($params[0]);
        return $vault->render();
    }

}

?>

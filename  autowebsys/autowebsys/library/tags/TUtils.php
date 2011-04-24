<?php

require_once('core/renderers/WindowRenderer.php');
require_once('tags/CustomTag.php');

class TUtils extends CustomTag {
    private static $uids = array();

    public function getRandomUID() {
        return WindowRenderer::getUID();
    }

    public function getRequestUID($names) {
        if(!isset($names[0])) {
            $names[0] = "default";
        }
        if(!isset(self::$uids[$names[0]])) {
            self::$uids[$names[0]] = WindowRenderer::getUID();
        }
        return self::$uids[$names[0]];
    }

}

?>

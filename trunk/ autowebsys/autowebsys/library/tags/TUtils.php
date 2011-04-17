<?php

require_once('core/renderers/WindowRenderer.php');
require_once('tags/CustomTag.php');

class TUtils extends CustomTag {
    private static $uid = -1;

    public function getRandomUID() {
        return WindowRenderer::getUID();
    }

    public function getRequestUID() {
        if(self::$uid == -1) {
            self::$uid = WindowRenderer::getUID();
        }
        return self::$uid;
    }
}

?>

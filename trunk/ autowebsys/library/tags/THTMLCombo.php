<?php

require_once('tags/CustomTag.php');
require_once('core/renderers/structures/HTMLCombo.php');
require_once('core/renderers/structures/HTMLComboNoBind.php');
require_once('core/Logger.php');

class THTMLCombo extends CustomTag {
    const LOG_TYPE = "THTMLCOMBO";

    public function parseCombo($params) {
        logger::info(self::LOG_TYPE, "Rendering combo(name:$params[0], bind:$params[1], onChange:$params[2], name:$params[3], result:$params[4])");
        $combo = new HTMLCombo($params[0], $params[1], $params[2], $params[3], $params[4]);
        return $combo->render();
    }

    public function parseComboNoBind($params) {
        logger::info(self::LOG_TYPE, "Rendering combo(name:$params[0], onChange:$params[1], name:$params[2], result:$params[3])");
        $combo = new HTMLComboNoBind($params[0], null, $params[1], $params[2], $params[3]);
        return $combo->render();
    }

}

?>

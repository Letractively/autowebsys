<?php

require_once('core/Translator.php');
require_once('tags/CustomTag.php');

class TTranslator extends CustomTag {
    public function getText($name) {
        return Translator::_($name);
    }
}
?>

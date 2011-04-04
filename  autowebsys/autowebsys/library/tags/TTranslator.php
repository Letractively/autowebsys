<?php

require_once('core/Translator.php');

class TTranslator {
    public function getText($name) {
        return Translator::_($name);
    }
}
?>

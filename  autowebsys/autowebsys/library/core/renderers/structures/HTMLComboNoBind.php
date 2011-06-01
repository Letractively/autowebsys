<?php

require_once('core/renderers/structures/HTMLCombo.php');

class HTMLComboNoBind extends HTMLCombo {
    
    protected function openSelect() {
        $this->addHTMLLine("<select id=\"$this->id\" onChange=\"$this->onChange('$this->id', '$this->resultId')\" style=\"width: 100%\">");
    }

}
?>

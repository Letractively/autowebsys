<?php

require_once('tags/CustomTag.php');

class TTemplate extends CustomTag {
    public function getTemplate($params) {
        $xml = XMLParser::xmlStringAsObject(ApplicationManager::getCachedValue(ApplicationManager::$DATA_TEMPLATE, $params[0]));
        return STParser::parse($xml->html->asXML());
    }
}
?>

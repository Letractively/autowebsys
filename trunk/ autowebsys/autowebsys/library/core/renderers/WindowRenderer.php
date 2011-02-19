<?php

require_once('core/Translator.php');
require_once('core/STParser.php');

class WindowRenderer {

    private static $defaultWidth = "100%";
    private static $defaultHeight = "150px";

    public static function generateXML($name) {
        header('Content-type: text/xml');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $window = ApplicationManager::getCachedValue(ApplicationManager::WINDOW_DESCRIPTION, $name);
        echo STParser::parse($window);
    }

//    public static function renderDescription($window) {
//        $out = "<window>";
//        foreach ($window->children() as $child) {
//            switch ($child->getName()) {
//                //case "content":
//                //    $out .= "<content>" . self::renderContent($child) . "</content>";
//                //    break;
//                default:
//                    $string = $child->__toString();
//                    $out .= "<" . $child->getName() . ">" . $string . "</" . $child->getName() . ">";
//            }
//        }
//        $out .= "</window>";
//        return $out;
//    }

    public static function renderContent($content) {
        $out = "";
        foreach ($content->children() as $child) {
            switch ($child->getName()) {
                case "html":
                    $xml = $child->asXML();
                    $xml = substr($xml, 6, count($xml) - 8);
                    $out .= $xml;
                    break;
            }
        }
        return $out;
    }

    public static function getUID() {
        return uniqid();
    }

}
?>

<?php

require_once('core/Translator.php');
require_once('core/STParser.php');

class MainMenuRenderer {

    public static function generateXML($request) {
        header('Content-type: text/xml');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $xmlString = ApplicationManager::getCachedValue(ApplicationManager::$INTERFACE_MAINMENU);
        $menu = XMLParser::xmlStringAsObject($xmlString);
        $menu = self::renderMenu($menu, $request);
        echo STParser::parse($menu);
    }

    public static function renderMenu($menu, $request) {
        $menu['id'] = 'start';
        $menu['text'] = Translator::_('MENU_START');
        $menu['img'] = 'start.gif';
        $menu = self::renderLevel($menu, $request);
        $menu = substr($menu, 0, -7);
        return "<menu>" .
        $menu .
        "<item id=\"logout\" text=\"" . Translator::_('MENU_LOGOUT') . "\" img=\"logout.gif\" />" .
        "</item></menu>";
    }

    private static function renderLevel($el, $request) {
        $renderedMenu = "";
        if (!isset($el['security']) || AuthManager::checkAccess($el['security'], $request)) {
            $renderedMenu .= "<item id=\"m_" . $el['id'] . "\" text=\"" . $el['text'] . "\" img=\"" . $el['img'] . "\">";
            foreach ($el as $child) {
                $renderedMenu .= MainMenuRenderer::renderLevel($child, $request);
            }
            $renderedMenu .= "</item>";
        }
        return $renderedMenu;
    }

}
?>

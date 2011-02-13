<?php

require_once('core/Translator.php');
require_once('core/STParser.php');

class MainMenuRenderer {

    public static function renderMenu($menu) {
        $menu['id'] = 'start';
        $menu['text'] = Translator::_('MENU_START');
        $menu['img'] = 'start.gif';
        $menu = self::renderLevel($menu);
        $menu = substr($menu, 0 , -7);
        return "<menu>".
            $menu.
            "<item id=\"logout\" text=\"".Translator::_('MENU_LOGOUT')."\" img=\"logout.gif\" />".
            "</item></menu>";
    }

    private static function renderLevel($el) {
        $el['text'] = $el['text'];
        $renderedMenu = "<item id=\"m_" . $el['id'] . "\" text=\"" . $el['text'] . "\" img=\"" . $el['img'] . "\">";
        foreach ($el as $child) {
            $renderedMenu .= MainMenuRenderer::renderLevel($child);
        }
        $renderedMenu .= "</item>";
        return $renderedMenu;
    }

}
?>

<?php

require_once('tags/CustomTag.php');
require_once('core/renderers/structures/HTMLTab.php');
require_once('core/Logger.php');

class TTab extends CustomTag {
    const LOG_TYPE = "TTab";

    public function parseTab($params) {
        logger::info(self::LOG_TYPE, "Rendering tabbar: " . implode($params));
        $tabs = $this->getTabs($params);
        $tab = new HTMLTab($tabs);
        return $tab->render();
    }

    private function getTabs($params) {
        $tabs = array();
        foreach ($params as $windowID) {
            $model = XMLParser::getWindowDescription($windowID);
            if (AuthManager::checkAccess($model->security, null, true)) {
                $tabs[] = array(
                    "url" => "/data/index/type/window-content/name/" . $windowID . $this->flatRequestParams($this->getRequestParams()),
                    "title" => STParser::parse($model->title),
                );
            }
        }
        return $tabs;
    }

}

?>
<?php

require_once('core/ApplicationManager.php');
require_once('core/renderers/ModelRenderer.php');
require_once('core/Logger.php');
require_once('tags/CustomTag.php');

class TModelsRenderer extends CustomTag {

    public function parseModel($params) {
        $out = "";
        $type = $params[0];
        $name = $params[1];
        $model = ApplicationManager::getCachedValue(ApplicationManager::$DATA_MODEL_SQL, $name);
        switch ($type) {
            case "grid":
                $out .= ModelRenderer::renderGrid($model, $this->getRequestParam('wid'));
                break;
            case "form":
                if ($this->hasRequestParam('id')) {
                    $out .= ModelRenderer::renderForm($model, $this->getRequestParam('wid'), $this->getRequestParam('id'));
                } else {
                    $out .= ModelRenderer::renderForm($model, $this->getRequestParam('wid'));
                }
                break;
            default:
                Logger::warning(self::$log_type, "Uknown model type: " . $type);
        }
        return $out;
    }

    public function parseTag($params) {
        $tabs = array();
        foreach($params as $windowID) {
            $model = XMLParser::getWindowDescription($windowID);
            $tabs[] = array(
                "url" => "/data/index/type/window-content/name/" . $windowID . $this->flatRequestParams(),
                //"url" => "/data/index/type/window-content/name/" . $windowID,
                "title" => STParser::parse($model->title),
            );
        }
        return ModelRenderer::renderTag($tabs);
    }

}
?>

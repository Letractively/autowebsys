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
        $wid = $this->getRequestParam('wid');
        //przy przepisywaniu argumentow wid sie dubluje, zazwyczaj chodzi o
        //nowe okno, czyli wiekszy wid
        if(is_array($wid)) {
            $wid = $wid[0] > $wid[1] ? $wid[0] : $wid[1];
        }
        switch ($type) {
            case "grid":
                $out .= ModelRenderer::renderGrid($model, $wid, $this->getRequestParams());
                break;
            case "form":
                if ($this->hasRequestParam('id')) {
                    $out .= ModelRenderer::renderForm($model, $wid, $this->getRequestParam('id'), $this->getRequestParams());
                } else {
                    $out .= ModelRenderer::renderForm($model, $wid, 0, $this->getRequestParams());
                }
                break;
            case "combo":
                $out .= ModelRenderer::renderCombo($model, $params[2]);
                break;
            case "tree":
                $out .= ModelRenderer::renderTree($model, $this->getRequestParams());
                break;
            case "layout":
                $out .= ModelRenderer::renderLayoutModel($model, $this->getRequestParams());
                break;
            default:
                Logger::warning("MODEL_RENDERER", "Uknown model type: " . $type);
        }
        return $out;
    }

    public function parseTabbar($params) {
        $tabs = array();
        foreach($params as $windowID) {
            $model = XMLParser::getWindowDescription($windowID);
            $tabs[] = array(
                "url" => "/data/index/type/window-content/name/" . $windowID . $this->flatRequestParams($this->getRequestParams()),
                "title" => STParser::parse($model->title),
            );
        }
        return ModelRenderer::renderTab($tabs);
    }

    public function parseLayout($params) {
        return STParser::parse(ModelRenderer::renderLayout($params[0], array_slice($params, 1), $this->flatRequestParams($this->getRequestParams())));
    }

}
?>

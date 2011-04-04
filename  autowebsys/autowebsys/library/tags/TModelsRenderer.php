<?php

require_once('core/ApplicationManager.php');
require_once('core/renderers/ModelRenderer.php');
require_once('core/Logger.php');

class TModelsRenderer {

    public function parseModel($params) {
        $out = "";
        $type = $params[0];
        $name = $params[1];
        $model = ApplicationManager::getCachedValue(ApplicationManager::$DATA_MODEL_SQL, $name);
        switch ($type) {
            case "grid":
                $out .= ModelRenderer::renderGrid($model, $params['wid']);
                break;
            case "form":
                if (isset($params['id'])) {
                    $out .= ModelRenderer::renderForm($model, $params['wid'], $params['id']);
                } else {
                    $out .= ModelRenderer::renderForm($model, $params['wid']);
                }
                break;
            default:
                Logger::warning(self::$log_type, "Uknown model type: " . $type);
        }
        return $out;
    }

}
?>

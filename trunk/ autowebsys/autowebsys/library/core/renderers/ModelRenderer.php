<?php

require_once('core/renderers/structures/Grid.php');
require_once('core/renderers/structures/Form.php');
require_once('core/renderers/structures/Tabbar.php');
require_once('core/renderers/structures/Tree.php');
require_once('core/renderers/structures/Layout.php');
require_once('core/renderers/WindowRenderer.php');
require_once('core/STParser.php');
require_once('tags/TUtils.php');

class ModelRenderer {

    private static $log_type = "MODEL_RENDERER";

    private static function buildDiv($id, $style = "width: 100%; height: 100%;") {
        return "<div id=\"$id\" style=\"$style\"></div>";
    }

    private static function adjustSizeToFullHeight($id, $offset = 30) {
        $out = "";
        $out .= "var el = document.getElementById('$id');";
        $out .= "el.style.height = (el.offsetHeight - $offset) + 'px';";
        return $out;
    }

    private static function openScript() {
        return "<script type=\"text/javascript\">";
    }

    private static function closeScript() {
        return "</script>";
    }

    public static function renderTab($windows) {
        $out = "";
        $tab = new Tabbar($windows);
        $out .= self::buildDiv($tab->name);
        $out .= self::openScript();
        $out .= self::adjustSizeToFullHeight($tab->name, 0);
        $out .= self::initTab($tab->name);
        $out .= self::addTabs($tab);
        $out .= self::closeScript();
        return $out;
    }

    private static function initTab($name) {
        $out = "$name = new dhtmlXTabBar('$name','top');";
        $out .= "$name.setImagePath('/dhtmlx/imgs/');";
        $out .= "$name.setSkin('dhx_skyblue');";
        $out .= "$name.setHrefMode('ajax-html');";
        return $out;
    }

    private static function addTabs($tab) {
        $name = $tab->name;
        $out = "";
        foreach ($tab->windows as $window) {
            $title = $window['title'];
            $url = $window['url'];
            $id = WindowRenderer::getUID();
            if ($out == "") {
                $out .= "$name.addTab('$id','$title', '100px');";
                $out .= "$name.setContentHref('$id','$url');";
                $out .= "$name.setTabActive('$id');";
            } else {
                $out .= "$name.addTab('$id','$title', '100px');";
                $out .= "$name.setContentHref('$id','$url');";
            }
        }
        return $out;
    }

    private static function buildTaskbarDiv($grid) {
        return (isset($grid->xml->taskbar) ? self::buildDiv($grid->taskbarName, "width: 100%; height: 30px;") : "");
    }

    private static function renameParameters($parametersXML, $parameters) {
        foreach ($parametersXML->children() as $rename) {
            $name = $rename['from']->__toString();
            $value = $parameters[$name];
            unset($parameters[$name]);
            $parameters[$rename['to']->__toString()] = $value;
        }
        return CustomTag::flatRequestParams($parameters);
    }

    private static function addButtons($toolbarName, $xml, $parameters) {
        $out = "";
        foreach ($xml->children() as $button) {
            $buttonName = $button->getName();
            switch ($buttonName) {
                case "separator":
                    $out .= "$toolbarName.addSeparator('separator');";
                    break;
                default:
                    $label = STParser::parse($button->label);
                    $out .= "$toolbarName.addButton('$buttonName', null, '$label', 'new.gif', 'new.gif');";
                    if (isset($button->window)) {
                        $window = $button->window;
                        if (isset($button->parameters)) {
                            $parameters = self::renameParameters($button->parameters, $parameters);
                            $out .= "$toolbarName.$buttonName" . "Window = '$window' + '$parameters';";
                            Logger::notice(self::$log_type, "Added button: $toolbarName.$buttonName" . "Window = '$window' + '$parameters';");
                        } else {
                            $out .= "$toolbarName.$buttonName" . "Window = '$window';";
                            Logger::notice(self::$log_type, "Added button: $toolbarName.$buttonName" . "Window = '$window';");
                        }
                    }
            }
        }
        return $out;
    }

    private static function buildTaskbar($grid, $taskbarName, $type, $parameters) {
        $out = "";
        if (isset($grid->xml->taskbar)) {
            $out .= "var $taskbarName = new dhtmlXToolbarObject('$taskbarName');";
        }
        return $out;
    }

    private static function initTaskbar($grid, $taskbarName, $type, $parameters) {
        $out = "";
        if (isset($grid->xml->taskbar)) {
            $out .= "$taskbarName.attachEvent('onClick', application.controlls.desktop.toolbarAction);";
            $out .= "$taskbarName.setIconsPath('/imgs/');";
            $out .= self::addButtons($taskbarName, $grid->xml->taskbar, $parameters);
        }
        return $out;
    }

    private static function buildGrid($name) {
        return "var $name = new dhtmlXGridObject('$name');";
    }

    private static function pinToTaskbar($grid, $taskbarName, $gridName) {
        return (isset($grid->xml->taskbar) ? "$taskbarName.subItem = $gridName;" : "");
    }

    private static function buildGridProcessor($processorName, $gridType, $gridName) {
        $out = "";
        $out .= "var $processorName = new dataProcessor('/data/processor/name/$gridType/type/grid/subtype/grid');";
        $out .= "$processorName.init($gridName);";
        return $out;
    }

    private static function buildTreeProcessor($processorName, $treeType, $treeName) {
        $out = "";
        $out .= "var $processorName = new dataProcessor('/data/processor/name/$treeType/type/tree/subtype/tree');";
        $out .= "$processorName.init($treeName);";
        return $out;
    }

    private static function addValidators($processorName, $validators) {
        $out = "";
        if (isset($validators) && $validators->count > 0) {
            foreach ($validators->children() as $validator) {
                $index = $validator->index;
                $function = $validator->function;
                $out .= "$processorName.setVerificator($index, $function);";
            }
        }
        return $out;
    }

    private static function pinProcessorToTaskbar($object, $processorName, $taskbarName) {
        if (isset($object->xml->taskbar)) {
            return "$taskbarName.processor = $processorName;";
        }
        return "";
    }

    private static function parseJS($object, $js) {
        $out = "";
        if (isset($js)) {
            foreach ($js->children() as $jsTag) {
                $parsedValue = STParser::parse($jsTag->__toString());
                $tagName = $jsTag->getName();
                $out .= "$object.$tagName($parsedValue);";
            }
        }
        return $out;
    }

    private static function initGrid($gridName, $gridType, $parameters) {
        $parameters = CustomTag::flatRequestParams($parameters);
        $out = "";
        $out .= "$gridName.init();";
        $out .= "$gridName.awsType = 'grid';";
        $out .= "$gridName.setAwaitedRowHeight(20);";
        $out .= "$gridName.enableSmartRendering(true, 100);";
        $out .= "$gridName.url = '/data/index/type/model/subtype/grid/name/$gridType' + '$parameters';";
        $out .= "$gridName.loadXML($gridName.url);";
        return $out;
    }

    private static function addMessage($xml, $gridName, $messageTag, $messageVar, $defaultMessage) {
        return (isset($xml->$messageTag) ? "$gridName.$messageVar = '" . STParser::parse($xml->$messageTag) . "';" : "$gridName.$messageVar = '$defaultMessage';");
    }

    private static function addMessages($xml, $gridName) {
        return self::addMessage($xml, $gridName, "not_selected_warn", "notSelectedWarn", "Select row first!")
        . self::addMessage($xml, $gridName, "confirm_delete", "confirmDelete", "Are you sure ?");
    }

    private static function setNames($gridName, $gridType) {
        return "$gridName.name = '$gridName';" . "$gridName.gName = '$gridType';";
    }

    private static function register($type, $object) {
        return "application.register.add('$type', $object);";
    }

    private static function addEvents($gridName) {
        $out = "";
        $out .= "$gridName.attachEvent('onXLS', function(grid_obj){";
        $out .= "   var el = document.getElementById('$gridName'); ";
        $out .= "   el.style.filter = 'alpha(Opacity=50)';";
        $out .= "   el.style.opacity = '0.5';";
        $out .= "}); ";
        $out .= "$gridName.attachEvent('onXLE', function(grid_obj){";
        $out .= "   var el = document.getElementById('$gridName'); ";
        $out .= "   el.style.filter = 'alpha(Opacity=100)';";
        $out .= "   el.style.opacity = '1';";
        $out .= "}); ";
        return $out;
    }

    public static function renderSQLGrid($xml, $wid, $parameters) {
        $grid = new Grid($xml);
        $out = "";
        $out .= self::buildTaskbarDiv($grid);
        $out .= self::buildDiv($grid->name, "width: 100%; height: 100%;");
        $out .= self::openScript();
        $out .= self::adjustSizeToFullHeight($grid->name, isset($grid->xml->taskbar) ? 30 : 0);
        $out .= self::buildTaskbar($grid, $grid->taskbarName, $grid->type, $parameters);
        $out .= self::initTaskbar($grid, $grid->taskbarName, $grid->type, $parameters);
        $out .= self::buildGrid($grid->name);
        $out .= self::addEvents($grid->name);
        $out .= self::pinToTaskbar($grid, $grid->taskbarName, $grid->name);
        $out .= self::buildGridProcessor($grid->processorName, $grid->type, $grid->name);
        $out .= self::addValidators($grid->processorName, $grid->xml->validators);
        $out .= self::pinProcessorToTaskbar($grid, $grid->processorName, $grid->taskbarName);
        $out .= self::parseJS($grid->name, $grid->xml->js);
        $out .= self::initGrid($grid->name, $grid->type, $parameters);
        $out .= self::addMessages($grid->xml->internationalization, $grid->name);
        $out .= self::setNames($grid->name, $grid->type);
        $out .= self::register($grid->type, $grid->name);
        $out .= self::closeScript();
        return $out;
    }

    public static function renderGrid($xml, $wid, $parameters) {
        $grid = new Grid($xml);
        Logger::notice(self::$log_type, "Rendering grid: " . $grid->type);
        switch ($grid->xml->type) {
            case "cc":
            case "sql":
                return self::renderSQLGrid($xml, $wid, $parameters);
                break;
            default:
                Logger::warning(self::$log_type, "Unknown model type: " . $grid->xml->type);
        }
    }

    private static function openForm($name) {
        return "<form action=\"\" method=\"post\" accept-charset=\"utf-8\" name=\"$name\" id=\"$name\" \">";
    }

    private static function closeForm() {
        return "</form>";
    }

    private static function createForm($name) {
        $out = "";
        $out .= "var $name = new dhtmlXForm(document.$name.name);$name.name = '$name';";
        return $out;
    }

    private static function addSaveEvent($formName, $processorName, $id) {
        return "document.$formName.save.onclick=function(){ validators.checkAndSend($formName, $processorName, $id);};";
    }

    private static function loadFormData($name, $type, $id) {
        return "$name.load('/data/index/type/model/subtype/form/name/$type?id=$id');";
    }

    private static function buildFormProcessor($processorName, $formName, $type, $id) {
        return "var $processorName = new dataProcessor('/data/processor/name/$type/type/form/subtype/form');$processorName.init($formName);";
    }

    private static function openFormEventAfterUpdate($processorName) {
        return "$processorName.attachEvent('onAfterUpdate', function(sid, action, tid, xml_node){";
    }

    private static function closeFormEventAfterUpdate() {
        return "return true;});";
    }

    private static function pinFormToProcessor($processorName, $formName) {
        return "$processorName.form = $formName;";
    }

    private static function parseFormJS($js, $parameters) {
        $js = STParser::parse($js, $parameters);
        if (isset($js)) {
            return "$js;";
        }
        return "";
    }

    private static function refreshForm($wid) {
        $out = "";
        $out .= "var url = application.controlls.desktop.getWindowURL($wid);";
        $out .= "var newUrl = '';";
        $out .= "if(url != null && url.lastIndexOf('=') == -1) {";
        $out .= "   newURL = url + '?id=' + xml_node.getAttribute('sid');";
        $out .= "   application.controlls.desktop.setWindowURL($wid, newURL);";
        $out .= "} else {";
        $out .= "   if(url != null) {";
        $out .= "   application.controlls.desktop.refreshWindow($wid);";
        $out .= "   }";
        $out .= "}";
        return $out;
    }

    private static function renderInputs($xml, $parameters) {
        $string = $xml->asXML();
        $string = substr($string, 6, count($string) - 8);
        return STParser::parse($string, $parameters);
    }

    public static function renderForm($xml, $wid, $id = 0, $parameters = array()) {
        $form = new Form($xml);
        Logger::notice(self::$log_type, "Rendering form: " . $form->name . ", id=" . $id);
        return ModelRenderer::renderSQLForm($form, $id, $wid, $parameters);
    }

    private static function renderSQLForm($form, $id, $wid, $parameters) {
        $out = "";
        $out .= self::openForm($form->name);
        $out .= self::renderInputs($form->template->html, $parameters);
        $out .= self::closeForm();
        $out .= self::openScript();
        $out .= self::createForm($form->name);
        $out .= self::register($form->type, $form->name);
        $out .= self::loadFormData($form->name, $form->type, $id);
        $out .= self::buildFormProcessor($form->processorName, $form->name, $form->type, $id);
        $out .= self::pinFormToProcessor($form->processorName, $form->name);
        $out .= self::addSaveEvent($form->name, $form->processorName, $id);
        $out .= self::openFormEventAfterUpdate($form->processorName);
        $out .= self::parseFormJS($form->xml->onSave, $parameters);
        $out .= self::refreshForm($wid);
        $out .= self::closeFormEventAfterUpdate();
        $out .= self::closeScript();
        return $out;
    }

    public static function renderComboWithoutBind($model, $eventHandler = "", $id = "", $resultHandlerIdLabel = "") {
        $model = XMLParser::xmlStringAsObject($model);
        $name = $model->name->__toString();
        Logger::notice(self::$log_type, "Rendering combo: " . $name . "event: $eventHandler, id: $id, resultHandlerid: $resultHandlerIdLabel");
        $utils = new TUtils();
        $target = $utils->getRequestUID(array($resultHandlerIdLabel));
        $id = $utils->getRequestUID(array($id));
        return "<select id=\"$id\" connector=\"/data/index/type/model/subtype/combo/name/$name\" onChange=\"$eventHandler('$id', '$target')\"></select>";
    }

    public static function renderCombo($model, $bind, $eventHandler = "", $id = "", $resultHandlerIdLabel = "") {
        $model = XMLParser::xmlStringAsObject($model);
        $name = $model->name->__toString();
        Logger::notice(self::$log_type, "Rendering combo: " . $name);
        $utils = new TUtils();
        $target = $utils->getRequestUID(array($resultHandlerIdLabel));
        $id = $utils->getRequestUID(array($id));
        return "<select id=\"$id\" connector=\"/data/index/type/model/subtype/combo/name/$name\" bind=\"$bind\" onChange=\"$eventHandler('$id', '$target')\"></select>";
    }

    private static function buildTree($tree) {
        $out = "";
        $out .= "var $tree->name = new dhtmlXTreeObject('$tree->name','100%','100%',0);";
        return $out;
    }

    private static function initTree($tree) {
        $out = "";
        $out .= "$tree->name.setXMLAutoLoading('/data/index/type/model/subtype/tree/name/$tree->type');";
        $out .= "$tree->name.loadXML('/data/index/type/model/subtype/tree/name/$tree->type');";
        $out .= "$tree->name.setImagePath('/dhtmlx/imgs/');";
        $out .= "$tree->name.awsType = 'tree';";
        $out .= "$tree->name.awsURL = '/data/index/type/model/subtype/grid/name/$tree->type';";
        return $out;
    }

    private static function addTreeEvents($name, $events) {
        $out = "";
        if (isset($events->on_select)) {
            $out .= "$name.attachEvent('onSelect', function(id){";
            $out .= "   var child = this.child; ";
            if ($events->on_select->type->__toString() == "grid") {
                $column = $events->on_select->filter_column->__toString();
                $out .= "this.child.filterBy($column, this.getSelectedItemText());";
            }
            $out .= "}); ";
        }
        return $out;
    }

    public static function renderTree($model, $parameters) {
        $tree = new Tree($model);
        $out = "";
        $out .= self::buildTaskbarDiv($tree);
        $out .= self::buildDiv($tree->name);
        $out .= self::openScript();
        $out .= self::adjustSizeToFullHeight($tree->name);
        $out .= self::buildTaskbar($tree, $tree->taskbarName, $tree->type, $parameters);
        $out .= self::initTaskbar($tree, $tree->taskbarName, $tree->type, $parameters);
        $out .= self::buildTree($tree);
        $out .= self::initTree($tree);
        $out .= self::register($tree->type, $tree->name);
        $out .= self::pinToTaskbar($tree, $tree->taskbarName, $tree->name);
        $out .= self::buildTreeProcessor($tree->processorName, $tree->type, $tree->name);
        $out .= self::addValidators($tree->processorName, $tree->xml->validators);
        $out .= self::pinProcessorToTaskbar($tree, $tree->processorName, $tree->taskbarName);
        $out .= self::parseJS($tree->name, $tree->xml->js);
        $out .= self::addMessages($tree->xml->internationalization, $tree->name);
        $out .= self::addTreeEvents($tree->name, $tree->xml->events);
        $out .= self::closeScript();
        return $out;
    }

    private static function buildLayout($name, $case) {
        $out = "";
        $out .= "var $name = new dhtmlXLayoutObject('$name', '$case');";
        return $out;
    }

    private static function attachWindowsToLayout($name, $windows) {
        $out = "";
        $index = 0;
        $cell = 97; // to jest 'a'
        foreach ($windows as $window) {
            $cellLetter = chr($cell++);
            $model = XMLParser::getWindowDescription($window);
            $windowName = $model->title->__toString();
            $out .= "var cell = $name.cells('$cellLetter');";
            $out .= "cell.setText('$windowName');";
            if (isset($model->width)) {
                $width = $model->width->__toString();
                $out .= "cell.setWidth($width);";
            }
            $out .= "cell.attachURL('/data/index/type/window-content/name/" . $window . $parameters . "', true);";
        }
        $out .= "";
        return $out;
    }

    private static function attachObjectsToLayout($name, $objects, $parameters) {
        $out = "";
        $child;
        $parent;
        foreach ($objects as $key => $value) {
            switch ($value->object->__toString()) {
                case "grid":
                    $cell = $value->name->__toString();
                    $modelName = $value->object_model->__toString();
                    $model = XMLParser::getXMLModel($modelName);
                    $grid = new Grid($model);
                    if(isset($grid->xml->taskbar)) {
                        $out .= "var o_toolbar_$name = $name.cells('$cell').attachToolbar();";
                        $out .= self::initTaskbar($grid, "o_toolbar_$name", $grid->type, $parameters);
                    }
                    $out .= "var o_grid_$name = $name.cells('$cell').attachGrid();";
                    $windowName = STParser::parse($value->title->__toString());
                    $out .= "$name.cells('$cell').setText('$windowName');";
                    if (isset($value->width)) {
                        $width = $value->width->__toString();
                        $out .= "$name.cells('$cell').setWidth($width);";
                    }
                    $grid->name = "o_grid_$name";
                    $out .= self::pinToTaskbar($grid, "o_toolbar_$name", $grid->name);
                    $out .= self::buildGridProcessor($grid->processorName, $grid->type, $grid->name);
                    $out .= self::addValidators($grid->processorName, $grid->xml->validators);
                    $out .= self::parseJS($grid->name, $grid->xml->js);
                    $out .= self::initGrid($grid->name, $grid->type, $parameters);
                    $out .= self::addMessages($grid->xml->internationalization, $grid->name);
                    $out .= self::setNames($grid->name, $grid->type);
                    $out .= self::register($grid->type, $grid->name);
                    switch ($value->hierarchy->__toString()) {
                        case "parent":
                            $parent = "o_grid_$name";
                            break;
                        case "child":
                            $child = "o_grid_$name";
                            break;
                        default:
                            Logger::warning(self::$log_type, "Unknown hierarchy: " . $value->hierarchy->__toString());
                    }
                    break;
                case "tree":
                    $cell = $value->name->__toString();
                    $modelName = $value->object_model->__toString();
                    $model = XMLParser::getXMLModel($modelName);
                    $tree = new Tree($model);
                    $out .= "var o_tree_$name = $name.cells('$cell').attachTree();";
                    $windowName = STParser::parse($value->title->__toString());
                    $out .= "$name.cells('$cell').setText('$windowName');";
                    if (isset($value->width)) {
                        $width = $value->width->__toString();
                        $out .= "$name.cells('$cell').setWidth($width);";
                    }
                    $tree->name = "o_tree_$name";
                    $out .= self::initTree($tree);
                    $out .= self::register($tree->type, $tree->name);
                    $out .= self::pinToTaskbar($tree, $tree->taskbarName, $tree->name);
                    $out .= self::buildTreeProcessor($tree->processorName, $tree->type, $tree->name);
                    $out .= self::addValidators($tree->processorName, $tree->xml->validators);
                    $out .= self::pinProcessorToTaskbar($tree, $tree->processorName, $tree->taskbarName);
                    $out .= self::parseJS($tree->name, $tree->xml->js);
                    $out .= self::addMessages($tree->xml->internationalization, $tree->name);
                    $out .= self::addTreeEvents($tree->name, $tree->xml->events);
                    switch ($value->hierarchy->__toString()) {
                        case "parent":
                            $parent = "o_tree_$name";
                            break;
                        case "child":
                            $child = "o_tree_$name";
                            break;
                        default:
                            Logger::warning(self::$log_type, "Unknown hierarchy: " . $value->hierarchy->__toString());
                    }
                    break;
                default:
                    Logger::warning(self::$log_type, "Unknown layout object type: " . $value->object->__toString());
            }
        }
        $out .= "$parent.child = $child;";
        return $out;
    }

    public static function renderLayout($case, $windows, $parameters) {
        $out = "";
        $name = "n_" . WindowRenderer::getUID();
        $out .= self::buildDiv($name);
        $out .= self::openScript();
        $out .= self::adjustSizeToFullHeight($name, 0);
        $out .= self::buildLayout($name, $case);
        $out .= self::attachWindowsToLayout($name, $windows, $parameters);
        $out .= self::closeScript();
        return $out;
    }

    public static function renderLayoutModel($model, $parameters) {
        $layout = new Layout($model);
        $out = "";
        $out .= self::buildDiv($layout->name);
        $out .= self::openScript();
        $out .= self::adjustSizeToFullHeight($layout->name, 0);
        $out .= self::buildLayout($layout->name, $layout->type);
        $out .= self::attachObjectsToLayout($layout->name, $layout->cells, $parameters);
        $out .= self::closeScript();
        return $out;
    }

}
?>

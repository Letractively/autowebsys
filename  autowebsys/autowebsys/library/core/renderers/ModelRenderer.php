<?php

require_once('core/renderers/structures/Grid.php');
require_once('core/renderers/structures/Form.php');
require_once('core/renderers/WindowRenderer.php');
require_once('core/STParser.php');

class ModelRenderer {

    private static $log_type = "MODEL_RENDERER";

    private static function buildDiv($id, $style) {
        return "<div id=\"$id\" style=\"$style\"></div>";
    }

    private static function openScript() {
        return "<script type=\"text/javascript\">";
    }

    private static function closeScript() {
        return "</script>";
    }

    private static function buildTaskbarDiv($grid) {
        return (isset($grid->xml->taskbar) ? self::buildDiv($grid->taskbarName, "width: 100%; height: 30px;") : "");
    }

    private static function addButtons($toolbarName, $xml) {
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
                        $out .= "$toolbarName.$buttonName" . "Window = '$window';";
                    }
            }
        }
        return $out;
    }

    private static function buildTaskbar($grid, $taskbarName, $type) {
        $out = "";
        if (isset($grid->xml->taskbar)) {
            $out .= "var $taskbarName = new dhtmlXToolbarObject('$taskbarName');";
            $out .= "$taskbarName.attachEvent('onClick', application.controlls.desktop.toolbarAction);";
            $out .= "$taskbarName.setIconsPath('/imgs/');";
            $out .= self::addButtons($taskbarName, $grid->xml->taskbar);
        }
        return $out;
    }

    private static function buildGrid($name) {
        return "var $name = new dhtmlXGridObject('$name');";
    }

    private static function pinGridToTaskbar($grid, $taskbarName, $gridName) {
        return (isset($grid->xml->taskbar) ? "$taskbarName.grid = $gridName;" : "");
    }

    private static function buildGridProcessor($processorName, $gridType, $gridName) {
        return "var $processorName = new dataProcessor('/data/processor/name/$gridType/type/grid/subtype/grid'); $processorName.init($gridName);";
    }

    private static function parseGridJS($gridName, $js) {
        $out = "";
        foreach ($js->children() as $js) {
            $parsedValue = STParser::parse($js->__toString());
            $tagName = $js->getName();
            $out .= "$gridName.$tagName('$parsedValue');";
        }
        return $out;
    }

    private static function initGrid($gridName, $gridType) {
        $out = "";
        $out .= "$gridName.init();";
        $out .= "$gridName.url = '/data/index/type/model/subtype/grid/name/$gridType';";
        $out .= "$gridName.load($gridName.url);";
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

    public static function renderSQLGrid($xml, $wid) {
        $grid = new Grid($xml);
        $out = "";
        $out .= self::buildTaskbarDiv($grid);
        $out .= self::buildDiv($grid->name, "width: 100%; height: 100%;");
        $out .= self::openScript();
        $out .= self::buildTaskbar($grid, $grid->taskbarName, $grid->type);
        $out .= self::buildGrid($grid->name);
        $out .= self::pinGridToTaskbar($grid, $grid->taskbarName, $grid->name);
        $out .= self::buildGridProcessor($grid->processorName, $grid->type, $grid->name);
        $out .= self::parseGridJS($grid->name, $grid->xml->js);
        $out .= self::initGrid($grid->name, $grid->type);
        $out .= self::addMessages($grid->xml->internationalization, $grid->name);
        $out .= self::setNames($grid->name, $grid->type);
        $out .= self::register($grid->type, $grid->name);
        $out .= self::closeScript();
        return $out;
    }

    public static function renderGrid($xml, $wid) {
        $grid = new Grid($xml);
        Logger::notice(self::$log_type, "Rendering grid: " . $grid->type);
        switch ($grid->xml->type) {
            case "cc":
            case "sql":
                return self::renderSQLGrid($xml, $wid);
                break;
            default:
                Logger::warning(self::$log_type, "Unknown model type: " . $grid->xml->type);
        }
    }

    private static function openForm($name) {
        return "<form action=\"\" method=\"post\" accept-charset=\"utf-8\" id=\"$name\">";
    }

    private static function closeForm() {
        return "</form>";
    }

    private static function createForm($name) {
        return "var $name = new dhtmlXForm('$name');$name.name = '$name';";
    }

    private static function loadFormData($name, $type, $id) {
        return "$name.load('/data/index/type/model/subtype/form/name/$type?id=$id');";
    }

    private static function buildFormProcessor($processorName, $formName, $type, $id) {
        return "var $processorName = new dataProcessor('/data/processor/name/$type/type/form/subtype/form');$processorName.init($formName);";
    }

    private static function openFormEvent($processorName) {
        return "$processorName.attachEvent('onAfterUpdate', function(sid, action, tid, xml_node){";
    }

    private static function addId($idName, $idValue) {
        return "<input type=\"hidden\" id=\"$idName\" value=\"$idValue\" bind=\"$idName\" />";
    }

    private static function closeFormEvent() {
        return "return true;});";
    }

    private static function parseFormJS($js) {
        if (isset($js)) {
            return "$js;";
        }
        return "";
    }

    private static function refreshForm($wid) {
        $out = "";
        $out .= "var url = application.controlls.desktop.getWindowURL($wid);";
        $out .= "if(url.lastIndexOf('=') == -1) {";
        $out .= "   var newURL = url + '?id=' + xml_node.getAttribute('sid');";
        $out .= "   application.controlls.desktop.setWindowURL($wid, newURL);";
        $out .= "} else {";
        $out .= "   application.controlls.desktop.refreshWindow($wid);";
        $out .= "}";
        return $out;
    }

    private static function renderInputs($xml) {
        $string = $xml->asXML();
        $string = substr($string, 6, count($string) - 8);
        return STParser::parse($string);
    }

    public static function renderForm($xml, $wid, $id = 0) {
        $form = new Form($xml);
        Logger::notice(self::$log_type, "Rendering form: " . $form->name . ", id=" . $id);
        if ($form->xml->type == "sql") {
            return ModelRenderer::renderSQLForm($form, $id, $wid);
        }
    }

    private static function renderSQLForm($form, $id, $wid) {
        $out = "";
        $out .= self::openForm($form->name);
        $out .= self::addId($form->xml->sql->id, $id);
        $out .= self::renderInputs($form->template->html);
        $out .= self::closeForm();
        $out .= self::openScript();
        $out .= self::createForm($form->name);
        $out .= self::register($form->type, $form->name);
        $out .= self::loadFormData($form->name, $form->type, $id);
        $out .= self::buildFormProcessor($form->processorName, $form->name, $form->type, $id);
        $out .= self::openFormEvent($form->processorName);
        $out .= self::parseFormJS($form->xml->onSave);
        $out .= self::refreshForm($wid);
        $out .= self::closeFormEvent();
        $out .= self::closeScript();
        return $out;
    }

}
?>

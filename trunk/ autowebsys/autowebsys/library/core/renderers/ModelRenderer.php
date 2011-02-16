<?php

require_once('core/renderers/WindowRenderer.php');
require_once('core/STParser.php');

class ModelRenderer {
    private static $log_type = "MODEL_RENDERER";

    public static function renderGrid($grid, $wid) {
        $xmlGrid = simplexml_load_string($grid);
        $uid = WindowRenderer::getUID();
        $gName = $xmlGrid->name;
        $name = $gName . $uid;
        $out = "";
        if (isset($xmlGrid->taskbar)) {
            $out .= "<div id=\"taskbar_$name\" style=\"width: 100%; height: 30px;\"></div>";
        }
        $out .= "<div id=\"$name\" style=\"width: 100%; height: 100%;\"></div>";
        $out .= "<script type=\"text/javascript\">";
        if (isset($xmlGrid->taskbar)) {
            $toolbarName = "toolbar" . $uid;
            $out .= "var $toolbarName = new dhtmlXToolbarObject('taskbar_$name');";
            $out .= "$toolbarName.attachEvent('onClick', application.controlls.desktop.toolbarAction);";
            $out .= "$toolbarName.setIconsPath('/imgs/');";
            if (isset($xmlGrid->taskbar->add)) {
                $window = $xmlGrid->taskbar->add->window;
                $out .= "$toolbarName.addWindow = '$window';";
            }
            if (isset($xmlGrid->taskbar->edit)) {
                $window = $xmlGrid->taskbar->edit->window;
                $out .= "$toolbarName.editWindow = '$window';";
            }
            foreach ($xmlGrid->taskbar->children() as $button) {
                $buttonName = $button->getName();
                switch ($buttonName) {
                    case "separator":
                        $out .= "$toolbarName.addSeparator('separator');";
                        break;
                    default:
                        $label = STParser::parse($button->label);
                        $out .= "$toolbarName.addButton('$buttonName', null, '$label', 'new.gif', 'new.gif');";
                }
            }
        }
        $gridName = "grid" . $uid;
        $out .= "var $gridName = new dhtmlXGridObject(\"" . $name . "\");";
        if (isset($xmlGrid->taskbar)) {
            $out .= "$toolbarName.grid = $gridName;";
        }
        $processorName = "processor" . $uid;
        $out .= "var $processorName = new dataProcessor('/data/processor/name/$gName/type/grid');";
        $out .= "$processorName.init($gridName);";
        foreach ($xmlGrid->js->children() as $js) {
            $parsedValue = STParser::parse($js->__toString());
            $tagName = $js->getName();
            $out .= "$gridName.$tagName('$parsedValue');";
        }
        $out .= "$gridName.init();";
        $out .= "$gridName.load(\"/data/index/type/model/subtype/grid/name/$gName\");";
        $out .= "$gridName.url = \"/data/index/type/model/subtype/grid/name/$gName\";";
        if (isset($xmlGrid->internationalization->not_selected_warn)) {
            $warn = STParser::parse($xmlGrid->internationalization->not_selected_warn);
            $out .= "$gridName.notSelectedWarn = '$warn';";
        } else {
            $out .= "$gridName.notSelectedWarn = 'Select row first!';";
        }
        if (isset($xmlGrid->internationalization->confirm_delete)) {
            $warn = STParser::parse($xmlGrid->internationalization->confirm_delete);
            $out .= "$gridName.confirmDelete = '$warn';";
        } else {
            $out .= "$gridName.confirmDelete = 'Are you sure ?';";
        }
        $out .= "$gridName.name = '$gridName';";
        $out .= "$gridName.gName = '$gName';";
        $out .= "application.register.add('$gName', $gridName);";
        $out .= "</script>";
        return $out;
    }

    public static function renderForm($form, $wid, $id = 0) {
        $out = "";
        $xmlForm = simplexml_load_string($form);
        Logger::notice(self::$log_type, "Rendering form: " . $xmlForm->name . ", id=" . $id);
        if ($xmlForm->type == "sql") {
            $out .= ModelRenderer::renderSQLForm($xmlForm, $id, $wid, $xmlForm->onSave);
        }
        return $out;
    }

    private static function renderSQLForm($form, $id, $wid, $js = null) {
        $out = "";
        $uid = WindowRenderer::getUID();
        $gName = $form->name;
        $formName = $gName . $uid;
        $IdName = $form->sql->id;
        $out .= "<form action=\"\" method=\"post\" accept-charset=\"utf-8\" id=\"$formName\">";
        foreach ($form->form->children() as $input) {
            switch ($input->type) {
                case "submit":
                    $label = STParser::parse($input->label);
                    $out .= "<td><input type=\"button\" command=\"save\" value=\"$label\" /></td>";
                    break;
                default:
                    $bind = $input->bind->__toString();
                    $label = STParser::parse($input->label->__toString());
                    $type = $input->type->__toString();
                    $out .= "<div>";
                    $out .= "<label>$label: </label><input class=\"dhxlist_txt_textarea\" bind=\"$bind\" type=\"$type\" />";
                    $out.= "</div>";
            }
        }
        $out .= "</form>";
        $out .= "<script type=\"text/javascript\">";
        $out .= "var $formName = new dhtmlXForm('$formName');";
        $out .= "$formName.name = '$formName';";
        $out .= "application.register.add('$gName', $formName);";
        $out .= "$formName.load('/data/index/type/model/subtype/form/name/$gName?id=$id');";
        $out .= "var dp = new dataProcessor('/data/processor/name/$gName/type/form?gr_id=$id');";
        $out .= "dp.init($formName);";
        if (isset($js)) {
            $out .= "dp.attachEvent('onAfterUpdate', function(sid, action, tid, xml_node){";
            $out .= "   $js;";
            $out .= "   application.controlls.desktop.refreshWindow($wid);";
            $out .= "   return true;";
            $out .= "});";
        }
        $out .= "</script>";
        return $out;
    }

}

?>

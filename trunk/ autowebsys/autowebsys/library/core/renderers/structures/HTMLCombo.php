<?php

require_once("core/renderers/structures/Structure.php");
require_once('tags/TUtils.php');

class HTMLCombo extends Structure {

    protected $onChange;
    protected $id;
    protected $resultId;

    public function HTMLCombo($modelName, $bind, $onChange, $name, $resultId) {
        parent::Structure($modelName);
        $this->bind = $bind;
        $this->onChange = $onChange;
        $this->generateResultId($resultId);
        $this->generateSelectName($name);
    }

    public function renderObject() {
        $this->openSelect();
        $this->addOptions(false);
        $this->closeSelect();
    }

    protected function openSelect() {
        $this->addHTMLLine("<select id=\"$this->id\" bind=\"$this->bind\" onChange=\"$this->onChange('$this->id', '$this->resultId')\" style=\"width: 100%\">");
    }

    protected function closeSelect() {
        $this->addHTMLLine("</select>");
    }

    protected function addOptions($addEmptyFirst) {
        $options = $this->getOptions();
        foreach ($options as $option) {
            $this->addOption($option);
        }
    }

    protected function getOptions() {
        return DBManager::getData($this->structureModel->sql->select->__toString());
    }

    protected function addOption($option) {
        $idName = $this->structureModel->sql->id->__toString();
        $columnName = $this->structureModel->sql->columns->__toString();
        $this->openOption($option->$idName);
        $this->addHTMLLine($option->$columnName);
        $this->closeOption();
    }

    protected function openOption($id) {
        $this->addHTMLLine("<option value=\"$id\">");
    }

    protected function closeOption() {
        $this->addHTMLLine("</option>");
    }

    protected function generateSelectName($key) {
        $utils = new TUtils();
        $this->id = $utils->getRequestUID(array($key));
    }

    protected function generateResultId($key) {
        $utils = new TUtils();
        $this->resultId = $utils->getRequestUID(array($key));
    }

}

?>

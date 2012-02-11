<?php

require_once("core/renderers/structures/Structure.php");
require_once('core/renderers/structures/Tabbar.php');
require_once('tags/TUtils.php');

class HTMLTab extends Structure {

    private $tab;
    private $active = false;

    public function HTMLTab($windows) {
        $this->tab = new Tabbar($windows);
    }

    public function renderObject() {
        $this->createDiv($this->tab->name, "width: 100%; height: 100%");
        $this->adjustSizeToFullHeight($this->tab->name, 0);
        $this->addWindows();
    }

    private function addWindows() {
        $this->addTabDeclaration();
        foreach ($this->tab->windows as $window) {
            $this->addTab($window);
        }
    }

    private function addTabDeclaration() {
        $name = $this->tab->name;
        $this->addJavaScriptLine("var $name = new dhtmlXTabBar('$name', 'top');\n");
        $this->addJavaScriptLine("$name.setImagePath('/dhtmlx/imgs/');\n");
        $this->addJavaScriptLine("$name.setSkin('dhx_skyblue');\n");
        $this->addJavaScriptLine("$name.setHrefMode('ajax-html');\n");
    }

    private function addTab($window) {
        $name = $this->tab->name;
        $id = WindowRenderer::getUID();
        $url = $window['url'];
        $title = $window['title'];
        $this->addJavaScriptLine("$name.addTab('$id', '$title', '100%');\n");
        $this->addJavaScriptLine("$name.setContentHref('$id','$url');\n");
        if(!$this->active) {
            $this->addJavaScriptLine("$name.setTabActive('$id');");
            $this->active = true;
        }
    }

    private function adjustSizeToFullHeight($id, $offset = 30) {
        $this->addJavaScriptLine("var el = document.getElementById('$id');\n");
        $this->addJavaScriptLine("el.style.height = (el.offsetHeight - $offset) + 'px';\n");
    }

}
?>
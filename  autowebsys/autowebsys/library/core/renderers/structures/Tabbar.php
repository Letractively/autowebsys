<?php

class Tabbar {
    public $windows;
    public $name;
    public $uid;

    function __construct($windows) {
        $this->windows = $windows;
        $uid = WindowRenderer::getUID();
        $this->uid = $uid;
        $this->name = "tab" . $uid;
    }

}
?>

<?php

class Group {

    private $groupName;
    private $groupParent;

    function __construct($groupName, $groupParent = null) {
        $this->groupName = $groupName;
        $this->groupParent = $groupParent;
    }

    public function getGroupName() {
        return $this->groupName;
    }

    public function setGroupName($groupName) {
        $this->groupName = $groupName;
    }

    public function getGroupParent() {
        return $this->groupParent;
    }

    public function setGroupParent($groupParent) {
        $this->groupParent = $groupParent;
    }

    public function hasParent() {
        return isset($this->groupParent);
    }

}
?>

<?php
class TRequestParameters extends CustomTag {
    public function getParameter($name) {
        return $this->getRequestParam($name[0]);
    }
}

?>

<?php

require_once('tags/CustomTag.php');
require_once('core/DBManager.php');

class TWID extends CustomTag {

    public function getWID() {
        $wid = $this->getRequestParam('wid');
        $min = -1;
        if (is_array($wid)) {
            foreach ($wid as $nr) {
                if ($nr > $min) {
                    $min = $nr;
                }
            }
            return $min;
        } else {
            return $wid;
        }
    }

}
?>

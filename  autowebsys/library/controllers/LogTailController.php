<?php

require_once('controllers/AbstractCustomController.php');
require_once('core/Logger.php');

class LogTailController extends AbstractCustomController {

    public function handleRequest() {
        $path = Logger::getLogPath();
        exec("tail -n20 $path/log.log", $output, $return);
        return implode("<br />", $output);
    }

}
?>

<?php

require_once('controllers/AbstractCustomController.php');
require_once('core/XMLParser.php');
require_once('core/structures/Tree.php');

class TreeController extends AbstractCustomController {

    public function handleRequest() {
        $model = XMLParser::getModel("tree_users_groups");
        $tree = new Tree();
        $tree->rebuildWholeTree($model);
        return "Users tree rebuilded";
    }

}
?>

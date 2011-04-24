<?php

class Tree {

    function rebuildWholeTree($model) {
        $root = DBManager::getData($model->sql->select_root);
        $idName = $model->sql->id->__toString();
        $rootID = $root[0]->$idName;
        $this->rebuildTree($model, $rootID);
    }

    function rebuildTree($model, $nodeID, $left = 1) {
        $idName = $model->sql->id->__toString();
        $parentIDName = $model->sql->parent_id->__toString();
        $leftName = $model->sql->lft->__toString();
        $rightName = $model->sql->rgt->__toString();
        $right = $left + 1;
        $children = DBManager::getData($model->sql->select_branch->__toString(), array($parentIDName => $nodeID));
        foreach ($children as $child) {
            $right = $this->rebuildTree($model, $child->$idName, $right);
        }
        DBManager::execute($model->sql->update_numbers->__toString(), array($leftName => $left, $rightName => $right, $idName => $nodeID));
        return $right + 1;
    }

}
?>

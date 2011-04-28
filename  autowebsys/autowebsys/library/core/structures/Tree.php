<?php

/**
 * Struktura dla drzewa o numerowanych węzłach. Pozwala na wyciągnięcie całego
 * poddrzewa jednym zapytaniem SQL. Numeruje się lewą i prawą 'krawędź' węzła.
 * Numerowanie odbywa się przy przechodzeniu drzewa od lewej do prawej,
 * lewa wartość jest wpisywana przy 'wejsciu' w węzeł, a prawa przy 'wyjściu'.
 * W ten sposób mamy informację, że węzły o numerach pomiedzy lewym a prawym
 * są częście poddrzewa.
 * Przykład selecta:
 * SELECT * FROM tree WHERE lft BETWEEN 2 AND 11;
 * Przy założeniu, że interesuje nas poddrzewo węzła o numerach 2 i 11,
 * dostaniemy wszystkiego jego podwęzły. Dodatkowo sortując po 'lft' dostaniemy
 * listę przedstawiającą strukturę 'rozwiniętego' drzewa. Daje nam to możliwość
 * odtworzenia struktury drzewa w czasie liniowym.
 * @author Tomasz 'lobo' Kopacki
 * @email tomasz@kopacki.eu
 */
class Tree {

    public function rebuildWholeTree($model) {
        $root = DBManager::getData($model->sql->select_root);
        $idName = $model->sql->id->__toString();
        $rootID = $root[0]->$idName;
        $this->rebuildTree($model, $rootID);
    }

    public function rebuildTree($model, $nodeID, $left = 1) {
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

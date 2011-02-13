<?php

class AuthAdapter implements AuthAdapterInterface {

    private $acl;

    public function __construct() {
        $this->acl = new Zend_Config_Ini(APPLICATION_PATH . "/configs/acl.ini");
    }

    public function authenticate($username, $password) {
        return true;
    }

    public function getRole($username) {
        return "member";
    }

    public function hasAccess($controller, $action, $role) {
        return $this->hasRoleAccessToResource($role, $controller, $action);
    }

    private function hasRoleAccessToResource($role, $controller, $action) {
        $allowedRole = $this->getAllowedRole($controller, $action);
        if ($this->isHigherOrEqualRole($role, $allowedRole)) {
            return true;
        }
        return false;
    }

    private function getAllowedRole($controller, $action) {
        $c = $this->acl->acl->resources->allow->__get($controller);
        if (isset($c->all)) {
            return $c->all;
        } else {
            return $c->__get($action);
        }
    }

    private function isHigherOrEqualRole($isHigher, $isLower) {
        if ($isHigher == null) {
            return false;
        }
        if ($isHigher == $isLower) {
            return true;
        }
        return $this->isHigherOrEqualRole($this->acl->acl->roles->__get($isHigher), $isLower);
    }

}
?>

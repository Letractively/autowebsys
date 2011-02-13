<?php

class PublicAdapter implements AuthAdapterInterface {

    public function authenticate($username, $password) {
        return true;
    }

    public function getRole($username) {
        return "public";
    }

    public function hasAccess($controller, $action, $role) {
        return true;
    }
}
?>

<?php

require_once("core/auth/AuthAdapter.php");
require_once("core/auth/Role.php");
require_once("core/auth/Group.php");

class PublicAdapter extends AuthAdapter {

    public function authenticate($username, $password) {
        return true;
    }

    public function getRole($username) {
        return new Role("public", "password", "public");
    }

    public function isUserInGroup(Role $role, $groupName) {
        return true;
    }


}
?>

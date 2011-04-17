<?php

require_once("core/DBManager.php");
require_once("core/ApplicationManager.php");
require_once("core/auth/Role.php");

class DBAdapter extends AuthAdapter {

    public function getRole($username) {
        $security = ApplicationManager::getCachedValue(ApplicationManager::$SECURITY_DB);
        $security = simplexml_load_string($security);
        $row = DBManager::getData($security->roles->queries->select->__toString(), array(
                    "www_login" => $username));
        if (count($row) == 1) {
            $row = $row[0];
            return new Role($row->www_login, $row->www_pass, 'admins');
        } else {
            return new Role();
        }
    }

    public function isUserInGroup(Role $role, $groupName) {
        return true;
    }

}
?>

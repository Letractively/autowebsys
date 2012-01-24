<?php

require_once("core/DBManager.php");
require_once("core/ApplicationManager.php");
require_once("adapters/auth/Role.php");

class AdvancedDBAdapter extends AuthAdapter {

    public function getRole($username) {
        $row = $this->getUserRow($username);
        return new Role($row->www_login, $row->www_pass, $row->name);
    }

    public function isUserInGroup(Role $role, $groupName) {
        $groups = $this->getUserGroups($role);
        return in_array($groupName, $groups);
    }

    private function getUserGroups($role) {
        $user = new Zend_Session_Namespace('user');
        if(!isset($user->groups)) {
            $user->groups = $this->getGroupsGraph($role->getGroup());
        }
        Logger::notice("AUTH_ADAPTER", "User(". $role->getUsername() .") groups: " . implode(", ", $user->groups));
        return $user->groups;
    }

    private function getGroupsGraph($group) {
        $groups = array();
        $groups[] = $group;
        $parents = $this->getParents($group);
        foreach ($parents as $parent) {
            if (!in_array($parent, $groups)) {
                $groups[] = $parent->name;
                $ancestors = $this->getGroupsGraph($parent->name);
                foreach ($ancestors as $ancestor) {
                    if (!in_array($ancestor, $groups)) {
                        $groups[] = $ancestor->name;
                    }
                }
            }
        }
        return $groups;
    }

    private function getParents($group) {
        $security = ApplicationManager::getCachedValue(ApplicationManager::$SECURITY_DB);
        $security = simplexml_load_string($security);
        return DBManager::getData($security->groups->queries->select->__toString(), array(
            "name" => $group));
    }

    private function getUserRow($username) {
        $security = ApplicationManager::getCachedValue(ApplicationManager::$SECURITY_DB);
        $security = simplexml_load_string($security);
        $rows = DBManager::getData($security->roles->queries->select->__toString(), array(
                    "www_login" => $username));
        return $rows[0];
    }

}
?>

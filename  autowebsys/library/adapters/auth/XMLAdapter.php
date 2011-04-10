<?php

require_once("core/ApplicationManager.php");
require_once("core/auth/Role.php");
require_once("core/auth/Group.php");

class XMLAdapter extends AuthAdapter {

    public function getRole($username) {
        $role = ApplicationManager::getCachedValue(ApplicationManager::$SECURITY_ROLES, $username);
        return new Role($role['id'], $role['password'], $role['group']);
    }

    public function getGroup($groupName) {
        $group = ApplicationManager::getCachedValue(ApplicationManager::$SECURITY_GROUPS, $groupName);
        return new Group($group['id'], isset($group['parent']) ? $group['parent'] : null);
    }

    public function isUserInGroup(Role $role, $groupName) {
        $group = $this->getGroup($groupName);
        return $this->recursiveUserInGroupCheck($role, $group);
    }

    private function recursiveUserInGroupCheck(Role $role, Group $group) {
        if ($role->getGroup() == $group->getGroupName()) {
            return true;
        } else {
            $mygroup = $this->getGroup($role->getGroup());
            if (!$mygroup->hasParent()) {
                return false;
            } else {
                $role->setGroup($mygroup->getGroupParent());
                return $this->recursiveUserInGroupCheck($role, $group);
            }
        }
    }

}
?>

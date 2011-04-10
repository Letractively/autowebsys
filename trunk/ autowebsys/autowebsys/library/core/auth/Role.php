<?php

class Role {

    private $username;
    private $password;
    private $group;

    function __construct($username = null, $password = null, $group = null) {
        $this->username = $username;
        $this->password = $password;
        $this->group = $group;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getGroup() {
        return $this->group;
    }

    public function setGroup($group) {
        $this->group = $group;
    }

    public function checkCredentials($username, $password) {
        return (isset($this->username) && $this->username == $username &&
                isset($this->password) && $this->password == $password);
    }

}
?>

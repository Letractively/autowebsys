<?php

require_once("core/FileManager.php");
require_once("core/auth/AuthManager.php");
require_once("core/Translator.php");

class IndexController extends Zend_Controller_Action {

    public function indexAction() {
        
    }

    public function loginAction() {
        if ($this->getRequest()->isPost()) {
            $username = $this->_request->getPost('username', null);
            $password = $this->_request->getPost('password', null);
            if (isset($username) && isset($password) && AuthManager::authenticate($username, $password)) {
                $this->_forward("index");
            }
        }
    }

    public function logoutAction() {
        AuthManager::logout();
        $this->_forward("index", "login");
    }

}

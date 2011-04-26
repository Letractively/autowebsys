<?php

require_once('core/Translator.php');

/**
 * Kontroler obsługi błędów. Można by dorobić jakieś przyjazne komunikaty dla
 * użytkownika końcowego.
 * @author Tomasz 'lobo' Kopacki
 * @email tomasz@kopacki.eu
 */
class ErrorController extends Zend_Controller_Action {

    public function errorAction() {
        $errors = $this->_getParam('error_handler');

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = Translator::getText('PAGE_NOT_FOUND');
                break;
            default:
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = Translator::getText('APPLICATION_ERROR');
                break;
        }

        $this->view->exception = $errors->exception;
        $this->view->request = $errors->request;
    }

    public function indexAction() {
        $this->getResponse()->setHttpResponseCode(401);
        $this->view->message = Translator::getText('NO_ACCESS');
        $this->view->controller = $this->_getParam("controller");
        $this->view->action = $this->_getParam("action");
    }

}
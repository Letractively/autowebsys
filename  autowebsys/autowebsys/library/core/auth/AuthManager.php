<?php

require_once("core/auth/AuthAdapter.php");
require_once("core/Logger.php");
require_once("core/Translator.php");

/**
 * Manager autoryzacji, na podstawie dostarczonego adaptera sprawdza, czy
 * użytkownik ma dostęp do żądanego zasobu. Adapter ustawia się w głównym
 * pliku konfiguracyjnym.
 * @author Tomasz 'lobo' Kopacki
 * @email tomasz@kopacki.eu
 */
class AuthManager extends Zend_Controller_Plugin_Abstract {

    private static $adapter = null;
    private static $request = null;
    private static $log_type = "AUTHORIZATION";

    /**
     * Metoda ustawiająca adapter, nie ma potrzeby wywoływać samodzielnie !
     * adapter jest inicjalizowany automatycznie na podstawie pliku konfiguracyjnego.
     * @param string $className nazwa adaptera autoryzacji
     * @return AuthManager instancja managera autoryzacji
     */
    public static function setAdapter($className) {
        require_once('adapters/auth/' . $className . '.php');
        self::$adapter = new $className;
        return new AuthManager();
    }

    /**
     * Uwierzytelnia użytkownika na podstawie podanych danych dostępowych
     * @param string $username nazwa użytkownika
     * @param string $password hasło w formie jawnej
     * @return boolean
     * true - jeśli podane dane są poprawne;
     * false - wpp
     */
    public static function authenticate($username, $password) {
        self::checkAdapter();
        if (self::$adapter->authenticate($username, $password)) {
            self::setSession($username);
            Logger::notice(self::$log_type, "User " . $username . " logged in");
            return true;
        }
        Logger::warning(self::$log_type, "User " . $username . " pass wrong credentials(password=$password)");
        return false;
    }

    /**
     * Wylogowywuje użytkownika i przekierowywuje na stronę logowania.
     */
    public static function logout() {
        Logger::notice(self::$log_type, "User " . AuthManager::getUsername() . " logged out");
        self::destroySession();
    }

    /**
     * Ustawia sesję użytkownika.
     * @param string $username nazwa użytkownika
     */
    private static function setSession($username) {
        $user = new Zend_Session_Namespace('user');
        // TODO: czas wygaśniecia sesji na podstawie konfiga głównego
        $user->auth = true;
        $user->username = $username;
    }

    /**
     * Niszczy sesję użytkownika
     */
    private static function destroySession() {
        $user = new Zend_Session_Namespace('user');
        $user->unsetAll();
    }

    /**
     * Zwraca rolę użytkownika na podstawie ustawionego adaptera
     * @return mixed rola użytkownika
     */
    public static function getUserRole() {
        self::checkAdapter();
        return self::$adapter->getRole(self::getUsername());
    }

    public static function checkAccess($privilagedGroupName, Zend_Controller_Request_Abstract $request) {
        $role = self::getUserRole();
        if (self::hasAccess($role, $privilagedGroupName)) {
            return true;
        } else {
            $controller = $request->getControllerName();
            $action = $request->getActionName();
            $type = $request->getParam("type");
            $name = $request->getParam("name");
            Logger::warning(self::$log_type, "User " . AuthManager::getUsername() . " tried to access forbidden zone: " . $controller . "/" . $action . "/" . $type . "/" . $name);
            return false;
        }
    }

    /**
     * Sprawdza dostęp do zasobu na podstawie ustawionego adaptera autoryzacji.
     * @param string $controller nazwa kontrolera
     * @param string $action nazwa akcji
     * @param string $role rola użytkownika
     * @return boolean
     * true - jeśli użytkownika ma dostęp do żądanego zasobu;
     * false - wpp
     */
    private static function hasAccess($role, $groupName) {
        self::checkAdapter();
        return self::$adapter->hasAccess($role, $groupName);
    }

    /**
     * Zwraca nazwę aktualnie zalogowanego użytkownika
     * @return string nazwa użytkownika
     */
    public static function getUsername() {
        $user = new Zend_Session_Namespace('user');
        return $user->username;
    }

    /**
     * Sprawdza czy użytkownika jest juz zalogowany
     * @return boolean
     * true - jeśli użytkownik jest zalogowany; false - wpp
     */
    private static function isAuthenticated() {
        $user = new Zend_Session_Namespace('user');
        return $user->auth;
    }

    /**
     * przekuwa parametry żądania na model autoryzacji. Nie wywoływać ręcznie !
     * Metoda używana przez silnik autowebsys
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        self::checkAdapter();
        self::$request = $request;
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        if (self::isAuthenticated()) {
            self::dispatchAccess($controller, $action);
        } else {
            self::dispatchError($controller, $action);
        }
    }

    /**
     * Sprawdza czy adapter autoryzacji został ustawiony, jeśli nie,
     * wyrzuca wyjątek
     */
    private static function checkAdapter() {
        if (self::$adapter == null) {
            throw new Exception('AuthAdapter not set');
        }
    }

    /**
     * Dodatkowe akcje związane z udzieleniem dostępu do zasobu. Aktualnie
     * sprawdza, czy użytkownik nie próbuje dostać się do strony logowania,
     * jeśli tak - przekierowywuje na stronę główną.
     * @param string $controller nazwa kontrolera
     * @param string $action nazwa akcji
     */
    private static function dispatchAccess($controller, $action) {
        if ($controller == 'index' && $action == 'login') {
            self::$request->setActionName('index');
        }
    }

    /**
     * Dodatkowe akcje związane z nie udzieleniem dostępu do zasobu.
     * Przekierowuje na stronę logowania
     * @param string $controller nazwa kontrolera
     * @param string $action nazwa akcji
     */
    private static function dispatchError($controller, $action) {
        self::$request->setControllerName('index');
        self::$request->setActionName('login');
        self::$request->setParam("controller", $controller);
        self::$request->setParam("action", $action);
    }

}
?>

<?php

/**
 * Interfejs adaptera autoryzacji. Dokładny opis implementacji dostępny
 * na wikipedi
 * @see http://code.google.com/p/autowebsys/wiki/Autoryzacja
 * @author Tomasz 'lobo' Kopacki
 * @email tomasz@kopacki.eu
 * @version 1.2
 */
abstract class AuthAdapter {

    /**
     * Metoda uwierzytelniająca
     * @param string username nazwa użytkownika
     * @param string password hasło w formie jawnej
     * @return boolean
     * true - jeśli podane dane są prawidłowe;
     * false - wpp
     */
    public function authenticate($username, $password) {
        $role = $this->getRole($username);
        return $role->checkCredentials($username, $password);
    }

    /**
     * Metoda sprawdza czy dany użytkownik ma dostęp do zasobów podanej grupy
     * @param string nazwa użytkownika
     * @param string nazwa grupy
     * @return boolean
     */
    public function hasAccess(Role $role, $groupName) {
        return $this->isUserInGroup($role, $groupName);
    }

    /**
     * Zwraca nazwę roli użytkownika
     * @param string nazwa użytkownika
     * @return Role funkcja musi zwracac obiekt typu Role lub null dla
     * błednej nazwy użytkownika
     */
    public abstract function getRole($username);

    /**
     * Funkcja sprawdza czy podany użytkownik należy do grupy
     * @param string nazwa użytkownika
     * @param string nazwa grupy
     * @return boolean
     * true - jeśli użytkownik należy do grupy
     * false - wpp
     */
    public abstract function isUserInGroup(Role $role, $groupName);

}
?>

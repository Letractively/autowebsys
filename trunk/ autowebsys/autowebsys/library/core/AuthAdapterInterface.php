<?php

/**
 * Interfejs adaptera autoryzacji. Dokładny opis implementacji dostępny
 * na wikipedi
 * @see http://code.google.com/p/autowebsys/wiki/Autoryzacja
 * @author Tomasz 'lobo' Kopacki
 * @email tomasz@kopacki.eu
 * @version 1.2
 */
interface AuthAdapterInterface {

    /**
     * Metoda uwierzytelniająca
     * @param string username nazwa użytkownika
     * @param string password hasło w formie jawnej
     * @return boolean
     * true - jeśli podane dane są prawidłowe;
     * false - wpp
     */
    public function authenticate($username, $password);

    /**
     * Zwraca nazwę roli użytkownika
     * @param string nazwa użytkownika
     * @return mixed rola
     */
    public function getRole($username);

    /**
     * Sprawdza, czy podana rola ma dostęp do zasobu
     * @param string type żądany typ zasobu
     * @param string resource nazwa zasobu
     * @param mixed role rola użytkownika
     * @return boolean
     * true - jeśli rola ma dostęp
     * false - wpp
     */
    public function hasAccess($type, $resource, $role);
}
?>

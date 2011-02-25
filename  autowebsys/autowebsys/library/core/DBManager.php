<?php

class DBManager {

    private static $dbh;
    private static $log_type = "DB";

    public static function fetchAll($query) {
        $statement = self::getConnector()->prepare($query);
        $statement->execute();
        return $statement->fetchALL(PDO::FETCH_OBJ);
    }

    public static function fetchRow($query) {
        $statement = self::getConnector()->prepare($query);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_OBJ);
    }

    public static function getData($queryName, $parameters = array()) {
        $query = ApplicationManager::getCachedValue(ApplicationManager::DB_QUERY, $queryName);
        Logger::notice(self::$log_type, "Getting data from named query: " . $query);
        $statement = self::getConnector()->prepare($query);
        foreach ($parameters as $key => $value) {
            Logger::notice(self::$log_type, "Binding: :" . $key . "='" . $value . "'");
            $statement->bindValue(':' . $key, $value);
        }
        $statement->execute();
        return $statement->fetchALL(PDO::FETCH_OBJ);
    }

    public static function execute($queryName, $parameters = array()) {
        $query = ApplicationManager::getCachedValue(ApplicationManager::DB_QUERY, $queryName);
        $statement = self::getConnector()->prepare($query);
        foreach ($parameters as $key => $value) {
            Logger::notice(self::$log_type, "Binding: :" . $key . "=" . $value);
            $statement->bindValue(':' . $key, $value);
        }
        $statement->execute();
        Logger::notice(self::$log_type, "SQL executed: " . $statement->queryString);
    }

    public static function insert($queryName, $parameters = array()) {
        self::getConnector()->beginTransaction();
        self::execute($queryName, $parameters);
        $id = self::getConnector()->lastInsertId();
        self::getConnector()->commit();
        return $id;
    }

    public static function getConnector() {
        if (self::$dbh == null) {
            try {
                $credentials = ApplicationManager::getCachedValue(ApplicationManager::DB_CREDENTIALS);
                self::$dbh = new PDO($credentials['url'], $credentials['user'], $credentials['password'], array(PDO::ATTR_PERSISTENT => true));
            } catch (PDOExceptione $e) {
                Logger::alert(self::$log_type, $e->getMessage());
            }
        }
        return self::$dbh;
    }

}
?>

<?php

class DBManager {
    private static $dbh;
    private static $log_type = "DB";

    public static function getData($queryName) {
        $query = ApplicationManager::getCachedValue(ApplicationManager::DB_QUERY, $queryName);
        $statement = self::getConnector()->query($query);
        return $statement->fetchALL(PDO::FETCH_CLASS, 'Object');
    }

    public static function execute($queryName, $parameters = array()) {
        $query = ApplicationManager::getCachedValue(ApplicationManager::DB_QUERY, $queryName);
        $statement = self::getConnector()->prepare($query);
        foreach($parameters as $key => $value) {
            Logger::notice(self::$log_type, "Binding: :" . $key . "=" . $value);
            $statement->bindValue(':'.$key, $value);
        }
        $statement->execute();
        Logger::notice(self::$log_type, "SQL executed: ". $statement->queryString);
    }

    public static function getConnector() {
        if(self::$dbh == null) {
            try {
                $credentials = ApplicationManager::getCachedValue(ApplicationManager::DB_CREDENTIALS);
                self::$dbh = new PDO($credentials['url'], $credentials['user'], $credentials['password'], array(PDO::ATTR_PERSISTENT => true));
            } catch(PDOExceptione $e) {
                Logger::alert(self::$log_type, $e->getMessage());
            }
        }
        return self::$dbh;
    }
}
?>

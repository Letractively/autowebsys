<?php

class DBManager {

    private static $dbh;
    private static $log_type = "DB";

    private static function checkForErrors($query, $statement) {
        $query = STParser::parse($query);
        if ($statement->errorCode() != "00000") {
            $errors = $statement->errorInfo();
            Logger::warning(self::$log_type, "Error in statement($query): " . implode(", ", $errors));
        } else {
            Logger::info(self::$log_type, "Statement executed without any errors.");
        }
    }
    
    public static function prepareStatement($queryName) {
        $query = ApplicationManager::getCachedValue(ApplicationManager::$DB_QUERY, $queryName);
        $query = STParser::parse($query);
        Logger::notice(self::$log_type, "Getting data from named query: " . $query);
        $statement = self::getConnector()->prepare($query);
        return $statement;
    }
    
    public static function executeStatement($queryName, $statement) {
        $statement->execute();
        self::checkForErrors($queryName, $statement);
        Logger::notice(self::$log_type, "SQL executed: " . $statement->queryString);
    }

    public static function fetchAll($query) {
        $query = STParser::parse($query);
        $statement = self::getConnector()->prepare($query);
        $statement->execute();
        self::checkForErrors($query, $statement);
        return $statement->fetchALL(PDO::FETCH_OBJ);
    }

    public static function fetchRow($query) {
        $query = STParser::parse($query);
        $statement = self::getConnector()->prepare($query);
        $statement->execute();
        self::checkForErrors($query, $statement);
        return $statement->fetch(PDO::FETCH_OBJ);
    }
    
    public static function getRow($queryName, $parameters = array()) {
        $result = self::getData($queryName, $parameters);
        if(count($result) == 1) {
            return $result[0];
        } else {
            Logger::warning(self::$log_type, "Got " . count($result) . " rows from $queryName. Expected single result");
            throw new Exception("Too many results");
            return null;
        }
    }

    public static function getData($queryName, $parameters = array()) {
        $query = ApplicationManager::getCachedValue(ApplicationManager::$DB_QUERY, $queryName);
        $query = STParser::parse($query);
        Logger::notice(self::$log_type, "Getting data from named query: " . $query);
        $statement = self::getConnector()->prepare($query);
        foreach ($parameters as $key => $value) {
            Logger::notice(self::$log_type, "Binding: :" . $key . "='" . $value . "'");
            $statement->bindValue(':' . $key, $value);
        }
        $statement->execute();
        self::checkForErrors($queryName, $statement);
        return $statement->fetchALL(PDO::FETCH_OBJ);
    }

    public static function execute($queryName, $parameters = array()) {
        $query = ApplicationManager::getCachedValue(ApplicationManager::$DB_QUERY, $queryName);
        $query = STParser::parse($query);
        $statement = self::getConnector()->prepare($query);
        foreach ($parameters as $key => $value) {
            Logger::notice(self::$log_type, "Binding: :" . $key . "=" . $value);
            $statement->bindValue(':' . $key, $value);
        }
        $statement->execute();
        self::checkForErrors($queryName, $statement);
        Logger::notice(self::$log_type, "SQL executed: " . $statement->queryString);
    }

    public static function insert($queryName, $sequenceName, $parameters = array()) {
        self::getConnector()->beginTransaction();
        self::execute($queryName, $parameters);
        $id = self::getConnector()->lastInsertId($sequenceName);
        Logger::notice(self::$log_type, "Last inserted ID: " . $id . ", from sequence: " . $sequenceName);
        self::getConnector()->commit();
        return $id;
    }

    public static function getConnector() {
        if (self::$dbh == null) {
            try {
                $credentials = ApplicationManager::getCachedValue(ApplicationManager::$DB_CREDENTIALS);
                self::$dbh = new PDO($credentials['url'], $credentials['user'], $credentials['password'], array(PDO::ATTR_PERSISTENT => true));
            } catch (PDOExceptione $e) {
                Logger::alert(self::$log_type, $e->getMessage());
            }
        }
        return self::$dbh;
    }

}
?>

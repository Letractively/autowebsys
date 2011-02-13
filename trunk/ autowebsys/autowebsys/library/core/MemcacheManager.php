<?php

require_once("core/Logger.php");

class MemcacheManager {

    private $memcache;
    private $state = false;
    private $log_type = "MEMCACHE";

    function __construct($server = "localhost", $port = 11211) {
        $this->memcache = new Memcache();
        if ($this->memcache->pconnect($server, $port)) {
            $this->state = true;
        } else {
            Logger::alert(self::$log_type, "Can't connect to memcache at " . $server . " on port " . $port);
        }
    }

    function set($name, $value) {
        if ($this->state) {
            $this->memcache->set($name, $value);
            return true;
        }
        return false;
    }

    function get($name) {
        if ($this->state) {
            return $this->memcache->get($name);
        }
        return null;
    }

}
?>

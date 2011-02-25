<?php

class FileManager {

    public static function getJSPath() {
        return APPLICATION_PATH . "/../../library/js/";
    }

        public static function getCSSPath() {
        return APPLICATION_PATH . "/../../library/css/";
    }

    private static function listFiles($dir) {
        return scandir($dir);
    }

    public static function generateJSHeaders() {
        $out = "";
        $path = self::getJSPath();
        foreach (self::listFiles($path) as $item) {
            if (is_file($path . $item)) {
                $item = substr($item, strrchr($item, "/"));
                $out .= "\t<script type=\"text/javascript\" src=\"/streamer/index/type/getjs/name/$item\"></script>\n";
            }
        }
        return $out;
    }

    public static function generateCSSHeaders() {
        $out = "";
        $path = self::getCSSPath();
        foreach (self::listFiles($path) as $item) {
            if (is_file($path . $item)) {
                $item = substr($item, strrchr($item, "/"));
                $out .= "\t<link rel=\"stylesheet\" type=\"text/css\" href=\"/streamer/index/type/getjs/name/$item\" />\n";
            }
        }
        return $out;
    }

}
?>

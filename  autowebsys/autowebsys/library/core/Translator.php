<?php

class Translator {

    private $locale;
    private $translate;
    private static $instance;

    private function init() {
        $this->locale = new Zend_Locale();
        $this->translate = new Zend_Translate('ini', APPLICATION_PATH . '/../../languages', null, array('scan' => Zend_Translate::LOCALE_DIRECTORY));
        if(!$this->translate->getAdapter()->isAvailable($this->locale->getLanguage())) {
            $this->locale->setLocale("en_US");
        }
        $this->translate->getAdapter()->setLocale($this->locale);
    }

    public static function getText($key) {
        if(self::$instance == null) {
            self::$instance = new Translator();
            self::$instance->init();
        }
        if (!self::$instance->translate->getAdapter()->isTranslated($key)) {
            return self::$instance->translate->_($key, "en");
        }
        return self::$instance->translate->_($key);
    }

    public static function _($name) {
        return Translator::getText($name);
    }
}
?>

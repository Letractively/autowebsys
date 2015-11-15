# Wymagania systemowe #

  * Serwer WWW - autowebsys przystosowany jest do [Apache'a](http://www.apache.org/). Do poprawnej obsługi systemu wymagany jest virtual host wskazujący na katalog ht\_root/autowebsys/public/ - znajduje się tam plik .htaccess z konfiguracją modułu Rewrite Engine. Przy konfiguracji virtual hosta należy pamietać o opcji `AllowOverride All`
  * Serwer [Memcache](http://memcached.org/) - wymagany do cachowania xml, serwer musi działać na standardowym porcie(11211)
  * [PHP](http://www.php.net/) w wersj 5.3.5
  * Biblioteki PHP do obsługi [Memcache'a](http://pecl.php.net/package/memcache)
  * Biblioteka [Pear Log](http://pear.php.net/package/Log/redirected)
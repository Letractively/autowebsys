<?php

set_include_path(implode(PATH_SEPARATOR, array(
            realpath(dirname(__FILE__) . '/../library'),
            realpath(dirname(__FILE__) . '/../../library'),
            get_include_path(),
        )));
defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', 'development');

require_once 'Zend/Application.php';

$application = new Zend_Application(
                APPLICATION_ENV,
                APPLICATION_PATH . '/configs/application.ini'
);

//require_once 'core/AuthManager.php';
//require_once 'core/Translator.php';
//require_once 'core/Logger.php';
//date_default_timezone_set('Europe/Warsaw');
//set_error_handler(array(new Logger(), "errorHandler"));
//$front = Zend_Controller_Front::getInstance();
//$front->registerPlugin(AuthManager::setAdapter($application->getOption('adapter')), 0);
require_once 'core/ApplicationManager.php';
ApplicationManager::initApplication();
$application->bootstrap();
$application->run();

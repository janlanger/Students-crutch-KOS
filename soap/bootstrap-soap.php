<?php

// absolute filesystem path to the web root
define('WWW_DIR', dirname(__FILE__).'/../www');

// absolute filesystem path to the application root
define('APP_DIR', WWW_DIR . '/../app');

// absolute filesystem path to the libraries
define('LIBS_DIR', WWW_DIR . '/../libs');
require APP_DIR.'/bootstrap.php';
//NDebug::enable(NDebug::PRODUCTION);
NDebug::$showBar=FALSE;
?>

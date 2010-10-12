<?php

// absolute filesystem path to the web root
define('CLI_DIR', dirname(__FILE__));
define('WWW_DIR', dirname(__FILE__).'/../document_root');

// absolute filesystem path to the application root
define('APP_DIR', WWW_DIR . '/../app');

// absolute filesystem path to the libraries
define('LIBS_DIR', WWW_DIR . '/../libs');
include_once APP_DIR.'/bootstrap.php';
$logger=NEnvironment::getService('ILogger');


$config=  NEnvironment::getConfig('xml');
try {
$downloader=NEnvironment::getContext()->getService('IDownloader');
/* @var $downloader CurlDownloader */
$downloader->setLocalRepository($config['localRepository'])
        ->setLogin($config['login'])
        ->setPassword($config['password'])
        ->setUrl($config['remoteURL']);


if($downloader->checkForNewer()==IDownloader::MODIFIED) {
    $return=$downloader->download();
    $logger->logMessage('Downloaded XML file - '.basename($return['file']).', '.NTemplateHelpers::bytes($return['size']).' (elapsed: '.round($return['time'].'s)', 3), Logger::INFO, 'XML Download');
}
else {
    $logger->logMessage('No newer file found.', Logger::INFO, 'XMLDownloader');
}
} catch (Exception $e) {
    $logger->logMessage($e->getMessage(),  Logger::CRITICAL, 'XMLDownloader');
    //NDebug::log($e);
}


?>

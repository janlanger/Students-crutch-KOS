<?php

// absolute filesystem path to the web root
define('CLI_DIR', dirname(__FILE__));
define('WWW_DIR', dirname(__FILE__) . '/../document_root');

// absolute filesystem path to the application root
define('APP_DIR', WWW_DIR . '/../app');

// absolute filesystem path to the libraries
define('LIBS_DIR', WWW_DIR . '/../libs');
include_once APP_DIR . '/bootstrap.php';
$logger = NEnvironment::getService('ILogger');


$config = NEnvironment::getConfig('xml');
try {
    $downloader = NEnvironment::getContext()->getService('IDownloader');
    /* @var $downloader CurlDownloader */
    $downloader->setLocalRepository($config['localRepository'])
            ->setLogin($config['login'])
            ->setPassword($config['password'])
            ->setUrl($config['remoteURL']);


    if ($downloader->checkForNewer() == IDownloader::MODIFIED) {
        $return = $downloader->download();
        $logger->logMessage('Downloaded XML file - ' . basename($return['file']) . ', ' . NTemplateHelpers::bytes($return['size']) . ' (elapsed: ' . round($return['time'], 3) . 's)', Logger::INFO, 'XMLDownloader-CLI');
    } else {
        $logger->logMessage('No newer file found.', Logger::INFO, 'XMLDownloader-CLI');
    }
} catch (Exception $e) {
    $logger->logMessage($e->getMessage(), Logger::CRITICAL, 'XMLDownloader-CLI');
    NDebug::log($e);
    exit;
}

if (isset($return['file'])) {
    //run import
    try {
        NDebug::timer("importer-total");
        $importer = NEnvironment::getService('IImporter');

        /* @var $importer XMLImporter */
        $importer->setFile(basename($return['file']));
        $tables = $importer->tables;
        foreach ($tables as $table) {
            /* @var $table XMLi_Entity */
            $table->setPrimaryKeys($table->getGuessedPrimaryKeys());
            $table->setIndexes($table->getGuessedIndexes());
        }
        $database_name = $config['liveDatabase'];
        $importer->buildDatabase($database_name, TRUE);
        $logger->logMessage("Database import sucessfull. Total time: " . round(NDebug::timer("importer-total"),3).'s', Logger::INFO,'XMLImport-CLI');
        
    } catch (Exception $e) {
        $logger->logMessage($e->getMessage(), Logger::CRITICAL,'XMLImport-CLI');
        NDebug::log($e);
        exit;
    }
}
?>

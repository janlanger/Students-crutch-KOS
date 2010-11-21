<?php
// absolute filesystem path to the web root
define('CLI_DIR', dirname(__FILE__));
define('WWW_DIR', dirname(__FILE__) . '/../www');

// absolute filesystem path to the application root
define('APP_DIR', WWW_DIR . '/../app');

// absolute filesystem path to the libraries
define('LIBS_DIR', WWW_DIR . '/../libs');
include_once APP_DIR . '/bootstrap.php';
$logger = \Nette\Environment::getService('ILogger');
Nette\Debug::$showLocation=TRUE;
Nette\Debug::$maxDepth = 5;

$config = \Nette\Environment::getConfig('xml');
if(!in_array('skip-download', $argv)) {
try {
    $downloader = \Nette\Environment::getContext()->getService('IDownloader');
    /* @var $downloader CurlDownloader */
  $downloader->setLocalRepository($config['localRepository'])
            ->setLogin($config['login'])
            ->setPassword($config['password'])
            ->setUrl($config['remoteURL']);


    if ($downloader->checkForNewer() == IDownloader::MODIFIED) {
        $return = $downloader->download();
        $logger->logMessage('Downloaded XML file - ' . basename($return['file']) . ', ' . Nette\Templates\TemplateHelpers::bytes($return['size']) . ' (elapsed: ' . round($return['time'], 3) . 's)', Logger::INFO, 'XMLDownloader-CLI');
    } else {
        $logger->logMessage('No newer file found.', Logger::INFO, 'XMLDownloader-CLI');
    }
} catch (Exception $e) {
    $logger->logMessage($e->getMessage(), Logger::CRITICAL, 'XMLDownloader-CLI');
    \Nette\Debug::log($e);
    exit;
}
$import_file=$return['file'];
} else {
    
    if(!isset($argv[2]) || !file_exists($argv[2])) {
        echo 'Parameter after skip-download must be an file to import.';
        exit (1);
    }
    $import_file=$argv[2];
}
if (isset($import_file)) {
    //run import
    try {
        \Nette\Debug::timer("importer-total");
        $importer = Nette\Environment::getService('IImporter');

        $importer->setFile($import_file);
       // $importer->buildDaatabase();
        /*$tables = $importer->tables;
        foreach ($tables as $table) {
          
            $table->setPrimaryKeys($table->getGuessedPrimaryKeys());
            $table->setIndexes($table->getGuessedIndexes());
        }*/
        $database_name = $config['liveDatabase'];
        $importer->buildDatabase($database_name, TRUE);
        $logger->logMessage("Database import sucessfull. Total time: " . round(\Nette\Debug::timer("importer-total"),3).'s', Logger::INFO,'XMLImport-CLI');
        
    } catch (Exception $e) {
        throw $e;
        $logger->logMessage($e->getMessage(), Logger::CRITICAL,'XMLImport-CLI');
        \Nette\Debug::log($e);
        exit;
    }
}
?>

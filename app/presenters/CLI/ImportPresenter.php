<?php

/**
 * Description of ImportPresenter
 *
 * @author Honza
 */
class ImportPresenter extends CliPresenter {

    private $file;
    private $logger;
    private $config;

    protected function startup() {
        parent::startup();
        $this->logger = $this->getApplication()->getService('ILogger');
        $this->config = \Nette\Environment::getConfig('xml');
    }

    public function actionDefault() {
        echo 'CLI Interface for revision import&update' . PHP_EOL . PHP_EOL;
        echo 'Usage:' . PHP_EOL;
        echo '[php] index.php [presenter:][action] [params]' . PHP_EOL . PHP_EOL;
        echo 'Actions:' . PHP_EOL;
        echo ' - default (or no action) - This help' . PHP_EOL;
        echo ' - download - Only downloads new rz.xml if available. (optional parameter --dont-check to skip checking for newer version)' . PHP_EOL;
        echo ' - import - Import rz.xml to the database and run the update afterwards. (optional param --file "filepath") for importing specific file. If no file is provided, new is dowloaded.' . PHP_EOL;
        echo ' - update - runs update from live database to revisions.' . PHP_EOL . PHP_EOL;
        $this->terminate();
    }

    public function actionDownload() {
        $this->processDownload();
        $this->terminate();
    }

    public function processDownload() {
        try {
            $params = $this->getRequest()->getParams();

            $downloader = \Nette\Environment::getContext()->getService('IDownloader');
            /* @var $downloader CurlDownloader */
            $downloader->setLocalRepository($this->config['localRepository'])
                    ->setLogin($this->config['login'])
                    ->setPassword($this->config['password'])
                    ->setUrl($this->config['remoteURL']);


            if (isset($params['dont-check']) || $downloader->checkForNewer() == IDownloader::MODIFIED) {
                $return = $downloader->download();
                $this->logger->logMessage('Downloaded XML file - ' . basename($return['file']) . ', ' . Nette\Templates\TemplateHelpers::bytes($return['size']) . ' (elapsed: ' . round($return['time'], 3) . 's)', Logger::INFO, 'XMLDownloader-CLI');
                $this->file = $return['file'];
                echo 'Downloaded file ' . $this->file . PHP_EOL;
            } else {
                $this->logger->logMessage('No newer file found.', Logger::INFO, 'XMLDownloader-CLI');
                echo 'Newest file is already downloded.' . PHP_EOL;
            }
        } catch (Exception $e) {
            $logger->logMessage($e->getMessage(), Logger::CRITICAL, 'XMLDownloader-CLI');
            \Nette\Debug::log($e);
            echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
            $this->terminate();
        }
    }

    public function actionImport($file=NULL) {
        if ($file == NULL) {
            $this->processDownload();
        } else {
            echo 'File provided, skiping download.' . PHP_EOL;
            $this->file = realpath($file);
        }
        $this->proccessImport($this->file);
        $this->terminate();
    }

    private function proccessImport($file) {
        try {
            \Nette\Debug::timer("importer-total");
            $importer = Nette\Environment::getService('IImporter');

            $importer->setFile($file);
            $database_name = $this->config['liveDatabase'];
            $importer->buildDatabase($database_name, TRUE);
            $time = round(\Nette\Debug::timer("importer-total"), 3);
            $this->logger->logMessage("Database import sucessfull. Total time: " . $time . 's', Logger::INFO, 'XMLImport-CLI');
            echo PHP_EOL . 'Import finished. Total time:' . $time . ' s' . PHP_EOL;
        } catch (Exception $e) {
            $this->logger->logMessage($e->getMessage(), Logger::CRITICAL, 'XMLImport-CLI');
            \Nette\Debug::log($e);
            exit;
        }
    }

    public function actionUpdate() {
        $manager=$this->getApplication()->getService('IDatabaseManager');
        $creator=new RevisionCreator();
        $creator->setManager($manager);
        $creator->setLive_database(\Nette\Environment::getConfig('xml')->liveDatabase);

        $toCreate=Revision::getToCreate();
        if(count($toCreate)) {
            //create rev
            foreach($toCreate as $revision) {
                $creator->create($revision);
            }
        }
        $this->terminate();
    }

}
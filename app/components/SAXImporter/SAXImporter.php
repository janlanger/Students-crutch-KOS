<?php

namespace SAX;
use SAX\Queue\ImportQueue;

/**
 * Description of SAXImporter
 *
 * @author Jan Langer, kontakt@janlanger.cz
 * @property-read SAXParser $parser
 */
class Importer extends \Nette\Object {

    //private $file;
    private $Parser;
    private $queue;

    public function getParser() {
        if ($this->Parser == NULL) {
            $this->Parser = \Nette\Environment::getContext()->getService('IParser');
        }
        return $this->Parser;
    }

    public function setFile($filename) {
        $this->parser->setFile($filename);
    }

    public function buildDatabase($db_name, $owerwrite=FALSE) {
        $tableCreator = \Nette\Environment::getContext()->getService('IDatabaseManager');
        if ($owerwrite) {
            $tableCreator->dropDatabase($db_name);
        } else {
            try {
                $tableCreator->setDefaultDatabase($db_name);
                throw new InvalidStateException("Database " . $db_name . ' already exists.');
            } catch (DibiException $e) {
                //intentionally
            }
        }
        $tableCreator->createDatabase($db_name);
        $tableCreator->setDefaultDatabase($db_name);
        
        $this->queue = new ImportQueue();
        $this->parser->setQueue($this->queue);
        $this->parser->run();
    }

}

?>

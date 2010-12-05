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

    /**
     * Instatinate parser (if needed) and returns it.
     * @internal
     * @return Parser
     */
    public function getParser() {
        if ($this->Parser == NULL) {
            $this->Parser = \Nette\Environment::getContext()->getService('IParser');
        }
        return $this->Parser;
    }

    /**
     * Sets parser file name
     * @param string $filename
     * @return Importer fluent
     */
    public function setFile($filename) {
        $this->parser->setFile($filename);
        return $this;
    }

    /**
     * Starts the import
     * @param string $db_name name of database for import
     * @param bool $owerwrite should owerwrite existent database?
     */
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

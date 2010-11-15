<?php

namespace SAX;

use XMLReader;

/**
 * Description of SAXParser
 *
 * @author Jan Langer, kontakt@janlanger.cz
 * @property-read \XMLReader $getReader()
 */
class Parser{

    private $file;
    private $reader;
    private $inTable;
    private $importQueue;

    public function setFile($file) {
        if (!file_exists(realpath($file))) {
            throw new FileNotFoundException('File "' . $file . '" was not found.');
        }
        $this->file = $file;
        return $this;
    }

    public function setQueue(ImportQueue $queue) {
        $this->importQueue = $queue;
    }

    public function initReader() {
        if ($this->reader == NULL) {
            if ($this->file == "") {
                throw new InvalidStateException("Unknown file to proccess.");
            }

            $this->reader = new XMLReader();

            \Nette\Debug::tryError();
            $this->reader->open(realpath($this->file));

            if (\Nette\Debug::catchError($msg)) {
                throw new InvalidArgumentException('Dokument není ve správném formátu. (SAX Error: ' . $msg . ')');
            }
        }
        return $this->reader;
    }

    public function run() {
        $r = $this->initReader();
        while ($r->read()) {
            if ($r->nodeType == XMLReader::ELEMENT) {
                if ($r->depth == 0) {   //root
                    $this->loadRootNode();
                    continue;
                }
                if ($r->depth == 1) { //table
                    $this->loadTable();
                    continue;
                }
            }
        }
        //dump($this->importQueue);
    }

    private function loadRootNode() {
        if ($this->reader->hasAttributes) {
            $table = new Entity\Entity();
            $table->setName($this->reader->localName);
            while ($this->reader->moveToNextAttribute()) {
//                $table->addColumn($this->reader->name);
                $table->addValue($this->reader->name, $this->reader->value);
            }
            $this->importQueue->add($table);
            $table->parseComplete();
        }
    }

    private function loadTable() {
        $table = new Entity\Entity();
        $table->setName($this->reader->name);
        while ($this->reader->read() && $this->reader->depth > 1) {
            if ($this->reader->nodeType == XMLReader::ELEMENT) {
                if ($this->reader->hasAttributes) {
                    while ($this->reader->moveToNextAttribute()) {
//                        $table->addColumn($this->reader->name);
                        $table->addValue($this->reader->name, $this->reader->value);
                    }
                }
                if ($this->reader->depth > 2 && $this->reader->nodeType==XMLReader::ELEMENT) {
//                    $table->addColumn($this->reader->name);
                    $table->addValue($this->reader->name, $this->reader->value);
                }
            }

            if ($this->reader->depth == 2) {
                if ($table->getNumOfRows() % 700 == 0 && $table->getNumOfRows() != 0) {
                    $this->importQueue->add($table);
                }
                $table->addRow();
            }
        }
        $table->parseComplete();
        $this->importQueue->add($table);
        /*if ($table->getName() == 'ucitele') {
            dump($this->importQueue);
            exit;
        }*/
    }

}

?>

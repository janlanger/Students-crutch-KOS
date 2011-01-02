<?php

namespace SAX;

use XMLReader;
use SAX\Queue\ImportQueue;

/**
 * Description of SAXParser
 *
 * @author Jan Langer, kontakt@janlanger.cz
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
                throw new \InvalidStateException("Unknown file to proccess. ".$this->file);
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
        $queue_size=$this->importQueue->getQueueSize()+1;
        $loop_counter=0;
        while($queue_size>0 && $queue_size>$this->importQueue->getQueueSize() && $loop_counter<2000) {
            $queue_size=$this->importQueue->getQueueSize();
            $this->importQueue->flush(TRUE);
            $loop_counter++;
            if($loop_counter % 10 == 0) {
                echo $loop_counter."\n";
            }
        }
        echo 'loops:'.$loop_counter."\n";
        echo 'size of queue:'.$queue_size."\n";
        echo 'memory peak: '.round(\memory_get_peak_usage()/1024/1024,2)." MB\n";
        echo 'memory system peak: '.round(\memory_get_peak_usage(TRUE)/1024/1024,2)." MB\n";
        echo 'queries:'.\dibi::$numOfQueries;
    }

    private function loadRootNode() {
        if ($this->reader->hasAttributes) {
            $table = new Entity\EntityDefinition();
            $table->setName($this->reader->localName);
            $entity=new Entity\Entity($table);
            while ($this->reader->moveToNextAttribute()) {
                $entity->add($this->reader->name, $this->reader->value);
            }
            $this->importQueue->add($entity);
            $table->parseComplete();
        }
    }

    private function loadTable() {
        $table = new Entity\EntityDefinition();
        $table->setName($this->reader->name);
        $entity=new Entity\Entity($table);
        while ($this->reader->read() && $this->reader->depth > 1) {
            if ($this->reader->nodeType == XMLReader::ELEMENT) {
                if ($this->reader->hasAttributes) {
                    while ($this->reader->moveToNextAttribute()) {
                        $entity->add($this->reader->name, $this->reader->value);
                    }
                }
                if ($this->reader->depth > 2 && $this->reader->nodeType==XMLReader::ELEMENT) {
                    $entity->add($this->reader->name, $this->reader->value);
                }
            }

            if ($this->reader->depth == 2) {
                $this->importQueue->add($entity);
                $entity=new Entity\Entity($table);
            }
        }
        $table->parseComplete();
        $this->importQueue->flush(TRUE);
    }

}

?>

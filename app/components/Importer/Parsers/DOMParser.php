<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DOMImporter
 *
 * @author Honza
 * @property-read DOMDocument $dom
 */
class DOMParser extends NObject {

    private $file;
    private $Dom;

    public function createStructure() {
        $tables = array();
        foreach ($this->dom->documentElement->childNodes as $node) {

            if ($node->nodeType == XML_ELEMENT_NODE) {
                $tables[strtolower($node->nodeName)] = XMLi_Entity::parseNode($node);
            }
        }

        $tables['rozvrh'] = XMLi_Entity::parseRootNode($this->dom->documentElement);
        return $tables;
    }

    public function getDom() {
        if ($this->Dom == NULL) {
            if ($this->file == "") {
                throw new InvalidStateException("Unknown file to proccess.");
            }
            
            $this->Dom=new DOMDocument();
            $this->Dom->preserveWhiteSpace = FALSE;
            \Nette\Debug::tryError();
            $this->Dom->load(realpath($this->file), LIBXML_NOEMPTYTAG | LIBXML_COMPACT);

            if (\Nette\Debug::catchError($msg)) {
                throw new InvalidArgumentException('Dokument není ve správném formátu. (DOM Error: ' . $msg . ')');
            }
        }
        return $this->Dom;
    }

    public function setFile($file) {
        if (!file_exists(realpath($file))) {
            throw new FileNotFoundException('File "' . $file . '" was not found.');
        }
        $this->file = $file;
        return $this;
    }

    public function getFile() {
        return $this->file;
    }

}

?>

<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of XML2SQL
 *
 * @author Honza
 * @property-read array $tables
 */
class XMLImporter extends NControl {

    private $rootNode = null;
    private $dom = NULL;
    private $tables;
    private $file;
    public static $status = array();

    public function setFile($filename) {
        $this->file = NEnvironment::getConfig('xml')->localRepository . '/' . $filename;
        if (!file_exists(realpath($this->file))) {
            throw new FileNotFoundException('File "' . $this->file . '" was not found.');
        }
    }

    private function loadFile() {
        if ($this->dom == NULL && $this->file != "") {
            $this->dom = new DOMDocument();
            $this->dom->preserveWhiteSpace = FALSE;
            NDebug::tryError();
            $this->dom->load(realpath($this->file), LIBXML_NOEMPTYTAG | LIBXML_COMPACT);

            if (NDebug::catchError($msg)) {
                throw new InvalidArgumentException('Dokument není ve správném formátu. (DOM Error: ' . $msg . ')');
            }
        }
    }

    public function getStructure() {
        XMLi_Entity::$cacheNamespace = 'xml_structure-' . basename($this->file);
        $cache = NEnvironment::getCache('xml_structure-' . basename($this->file));
        if (isset($cache['structure'])) {
            return $this->tables = $cache['structure'];
        }
        $this->loadFile();

        foreach ($this->dom->documentElement->childNodes as $node) {

            if ($node->nodeType == XML_ELEMENT_NODE) {
                $this->tables[strtolower($node->nodeName)] = XMLi_Entity::parseNode($node);
            }
        }
        $this->tables['rozvrh'] = XMLi_Entity::parseRootNode($this->dom->documentElement);
        $cache->save('structure', $this->tables, array(
            'expire' => time() + 5 * 3600,
            'tags' => array('xml'),
                'sliding' => TRUE
        ));
        return $this->tables;
    }

    
    public function buildDatabase($db_name) {
        //dibi::query("DROP DATABASE IF EXISTS [".$db_name."]");
        try {
            dibi::query("USE [" . $db_name . "]");
            throw new InvalidStateException("Database " . $db_name . ' already exists.');
        } catch (DibiException $e) {
            //intentionally
        }
        try {
            dibi::query("CREATE DATABASE [" . $db_name . "] COLLATE 'utf8_czech_ci'");
            dibi::query("USE [" . $db_name . "]");
            dibi::query("SET foreign_key_checks = 0");

            foreach ($this->tables as $table) {
                $time = microtime(TRUE);
                $table->createTable();
                self::$status[$table->name]['create_time'] = microtime(TRUE) - $time;
            }
            $table=microtime(TRUE);
            foreach ($this->tables as $table) {
                $table->createReferences();
            }
            self::$status['Cizí klíče']['create_time'] = microtime(TRUE) - $time;

            //dibi::query("SET foreign_key_checks = 1");
            dibi::insert("rozvrh_main.import_history", array(
                "filename" => basename($this->file),
                "database_name" => $db_name
            ))->execute();
            return TRUE;
        } catch (DibiException $e) {
            //rollback
            dibi::query("DROP DATABASE IF EXISTS [" . $db_name . "]");
            throw $e;
        }
    }

    public function getTables() {
        if ($this->tables == null) {
            $this->analyzeStructure();
        }
        return $this->tables;
    }

    public function getFile() {
        return $this->file;
    }

    public function getReport() {
        $ret = '';
        $total=0;
        foreach (self::$status as $key => $value) {
            $total+=round($value['create_time']*1000,4);
            $ret.=NHtml::el("li")->setText('"' . $key . '" - ' . round($value['create_time']*1000,4) . 'ms');
        }
        $ret.=NHtml::el("li")->setText('Celkem provedeno dotazů: ' . dibi::$numOfQueries);
        $ret.=NHtml::el("li")->setText('Celkový čas: ' . round($total/1000,3).'s');
        return NHtml::el("ul")->setHtml($ret);
    }

}

?>

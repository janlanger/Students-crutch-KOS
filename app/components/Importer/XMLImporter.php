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
 * @property-read DOMParser $parser
 */
class XMLImporter extends NControl {

    private $rootNode = null;
    private $dom = NULL;
    private $tables;
    private $file;
    public static $status = array();
    private $Parser = NULL;
    public static $cacheNamespace;

    public function getParser() {
        if($this->Parser == NULL) {
            $this->Parser= NEnvironment::getContext()->getService('IParser');
        }
        return $this->Parser;
    }

    public function setFile($filename) {
        $this->parser->setFile(NEnvironment::getConfig('xml')->localRepository . '/' . $filename);
    }

    public function getStructure() {
        self::$cacheNamespace = 'xml_structure-' . basename($this->parser->file);
        $cache = NEnvironment::getCache(self::$cacheNamespace);
        if (isset($cache['structure'])) {
            return $this->tables = $cache['structure'];
        }

        $this->tables=$this->parser->createStructure();
        
        $cache->save('structure', $this->tables, array(
            'expire' => time() + 5 * 3600,
            'tags' => array('xml'),
            'sliding' => TRUE
        ));
        return $this->tables;
    }

    public function buildDatabase($db_name, $owerwrite=FALSE) {
        $tableCreator = NEnvironment::getContext()->getService('IDatabaseManager');
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
        try {
            $tableCreator->createDatabase($db_name);
            $tableCreator->setDefaultDatabase($db_name);
            $tableCreator->fillDatabase($this->tables);

            dibi::insert(":main:import_history", array(
                "filename" => basename($this->file),
                "database_name" => $db_name
            ))->execute();
            return TRUE;
        } catch (DibiException $e) {
            //rollback
            $tableCreator->dropDatabase($db_name);
            throw $e;
        }
    }

    public function getTables() {
        if ($this->tables == null) {
            $this->getStructure();
        }
        return $this->tables;
    }

    public function getFile() {
        return $this->file;
    }

    public function getReport() {
        $ret = '';
        $total = 0;
        foreach (self::$status as $key => $value) {
            $total+=round($value['create_time'] * 1000, 4);
            $ret.=NHtml::el("li")->setText('"' . $key . '" - ' . round($value['create_time'] * 1000, 4) . 'ms');
        }
        $ret.=NHtml::el("li")->setText('Celkem provedeno dotazů: ' . dibi::$numOfQueries);
        $ret.=NHtml::el("li")->setText('Celkový čas: ' . round($total / 1000, 3) . 's');
        return NHtml::el("ul")->setHtml($ret);
    }

}

?>

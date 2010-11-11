<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of X2S_DataTable
 * @property-read array $guessedIndexes
 * @property-read array $columns
 * @property-read array $primaryKeys
 * @author Honza
 */
class XMLi_Entity extends \Nette\Object {

    private $name = NULL;
    private $columns = array();
    private $rows = array();
    private $guessedIndexes = array();
    private $indexes = array();
    private $foreignKeys = null;
    private $primaryKeys = null;

    public function __construct() {
        
    }

    /**
     *
     * @param DOMNode $node
     * @param bool $includeChilds
     * @return X2S_DataTable
     */
    public static function parseNode(DOMNode $node) {
        $_this = new self();
        $_this->name = $node->nodeName;
        $_this->analyzeNode($node);
        return $_this;
    }

    public static function parseRootNode(DOMNode $node) {
        $_this = new self();
        $_this->name = $node->nodeName;
        if ($node->hasAttributes()) {
            for ($i = 0, $attrs = $node->attributes; $i < $attrs->length; $i++) {
                $_this->columns[$attrs->item($i)->name] = new XMLi_Column($attrs->item($i)->name, "varchar(255)");
                $_this->rows[0][$attrs->item($i)->name] = $attrs->item($i)->value;
            }
        }
        $_this->columns['import_time'] = new XMLi_Column('import_time', "datetime");
        $_this->rows[0]['import_time'] = date("Y-m-d H:i:s");
        \Nette\Environment::getCache(XMLImporter::$cacheNamespace)->save($node->nodeName, $_this->rows, array(
            'expire' => time() + 5 * 3600,
            'tags' => array('xml')
        ));
        return $_this;
    }

    private function analyzeNode(DOMNode $node) {
        $storeData = TRUE;
        $cache = \Nette\Environment::getCache(XMLImporter::$cacheNamespace);
        if (isset($cache[$node->nodeName])) {
            $this->rows = $cache[$node->nodeName];
            $storeData = FALSE;
        }
        $row = 0;
        foreach ($node->childNodes as $child) {
            if ($child->nodeType != XML_ELEMENT_NODE)
                continue;


            if ($child->hasAttributes()) {
                $attrs = $child->attributes;

                for ($i = 0; $i < $attrs->length; $i++) {
                    $attribute = $attrs->item($i);
                    $attributeName = str_replace(".", "_", $attribute->name);
                    if ($storeData) {
                        $this->rows[$row][$attributeName] = ($attribute->value);
                    }

                    if (!isset($this->columns[$attributeName])) {
                        $this->columns[$attributeName] = new XMLi_Column($attributeName);
                    }
                    $this->columns[$attributeName]->detectType($attribute->value);


                    if (\Nette\String::endsWith($attributeName, "_id") && !in_array($attributeName, $this->guessedIndexes)) {
                        $this->guessedIndexes[] = $attributeName;
                    }
                }
            }
            if ($child->hasChildNodes()) {
                foreach ($child->childNodes as $children) {
                    if ($children->nodeType != XML_ELEMENT_NODE) {
                        continue;
                    }
                    if ($storeData) {
                        $this->rows[$row][$children->nodeName] = \Nette\String::trim($children->nodeValue);
                    }
                    if (!in_array($children->nodeName, $this->columns))
                        $this->columns[$children->nodeName] = new XMLi_Column($children->nodeName, 'text');
                }
            }
            $row++;
        }
        foreach ($this->rows as $key => $row) {
            foreach ($this->columns as $column) {
                if (!isset($row[$column->name])) {
                    $row[$column->name] = NULL;
                }
            }
            ksort($row);
            $this->rows[$key] = $row;
        }
        ksort($this->columns);
        if ($storeData) {
            $cache->save($node->nodeName, $this->rows, array(
                'expire' => time() + 5 * 3600,
                'tags' => array('xml'),
                'sliding' => TRUE
            ));
        }
        $this->rows = array();
    }

    public function getGuessedPrimaryKeys() {
        if (isset($this->columns['id']) && isset($this->columns['sem_id']) && !isset($this->columns['predmet_id'])) {
            //vyjimka pro tabulku predmety
            return array('id', 'sem_id');
        } elseif (isset($this->columns['id']) && isset($this->columns['stud_id'])) {
            //vyjimka pro tabulku studenti
            return array('id', 'stud_id');
        } elseif (isset($this->columns['id'])) {
            return array('id');
        } /* elseif (count($this->columns) == count($this->guessedIndexes)) {
          return $this->guessedIndexes;
          } */ else {
            return array();
        }
    }

    public function getIndexes() {
        return $this->indexes;
    }


    public function getName() {
        return strtolower($this->name);
    }

    public function getColumns() {
        return $this->columns;
    }

    public function getForeignKeys() {
        return $this->foreignKeys;
    }

    public function getRows() {
        $cache = \Nette\Environment::getCache(XMLImporter::$cacheNamespace);
        if (isset($cache[$this->name])) {
            return $cache[$this->name];
        }
    }

    public function getPrimaryKeys() {
        return $this->primaryKeys;
    }

    public function getGuessedIndexes() {

        return $this->guessedIndexes;
    }

    public function setIndexes($indexes) {
        $this->indexes = $indexes;
    }

    public function setForeignKeys($foreignKeys) {
        $this->foreignKeys = $foreignKeys;
    }

    public function setPrimaryKeys($primaryKeys) {
        $this->primaryKeys = $primaryKeys;
    }

    
    public function addPrimary($column) {
        $this->primaryKeys[] = $column;
    }

    public function addIndex($column) {
        $this->indexes[] = $column;
    }

    public function addForeign($column, $reference) {
        $this->foreignKeys[] = array('column' => $column, 'reference' => $reference);
    }

}

?>

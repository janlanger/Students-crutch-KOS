<?php

namespace SAX\Entity;

/**
 * Description of Entity
 *
 * @author Jan Langer, kontakt@janlanger.cz
 */
class EntityDefinition {

    private $name;
    private $columns = array();
    private $primaryKeys = array();
    private $indexes = array();
    private $foreigns = array();
    public $createDelayed = FALSE;
    public $tableCreated = FALSE;
    private $completeParse = FALSE;
    public $alterTable = array();

    public function __construct() {
    }
    
    public function free() {
        $this->columns=NULL;
    }

    public function __destruct() {
        $this->free();
    }

    public function addColumn($column) {
        if (!isset($this->columns[$column])) {
            $this->columns[$column] = new Column($column, $this);
            if($this->tableCreated) {
                $this->alterTable['add'][$column]=TRUE;
            }
        }
    }

    public function getName() {
        return $this->name;
    }
    public function isColumnExists($column) {
        return isset($this->columns[$column]);
    }

    public function setName($name) {
        $this->name = strtolower($name);
    }

    public function getColumns() {
        return $this->columns;
    }

    public function getColumn($key) {
        return $this->columns[$key];
    }

    public function hasColumn($key) {
        return isset($this->columns[$key]);
    }

    public function addPrimaryKey($k) {
        if(!\in_array($k, $this->primaryKeys)) {
            if($k!="id") {
                $this->addIndex($k);
            }
            $this->primaryKeys[] = $k;
        }
    }

    public function addIndex($k) {
        if(!\in_array($k, $this->indexes))
            $this->indexes[] = $k;
    }

    public function addForeignKey($k, $c) {
        if(!isset($this->foreigns[$k])) {
            $this->foreigns[$k] = array("column" => $k, "foreign" => $c);
            if(!isset($this->columns[$k])) {
                $this->addColumn($k);
                $this->createDelayed=TRUE;
            }
        }
    }
    public function getPrimaryKeys() {
        return $this->primaryKeys;
    }

    public function getIndexes() {
        return $this->indexes;
    }

    public function getForeigns() {
        return $this->foreigns;
    }

    public function isParseCompleted() {
        return $this->completeParse;
    }

    public function parseComplete() {
        $this->completeParse = TRUE;
    }

}

?>
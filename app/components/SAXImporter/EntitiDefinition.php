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
    public $dependecies;
    public $rows = array();
    public $createDelayed = FALSE;
    public $tableCreated = FALSE;
    private $indexCache = array();
    private $completeParse = FALSE;
    public $alterTable = array();

    public function __construct() {
        /*$this->rows[0] = array();
        $this->rowId= 0;*/
    }

    public function addColumn($column) {
        if (!isset($this->columns[$column])) {
            $this->columns[$column] = new Column($column, $this);
            if($this->tableCreated) {
                $this->alterTable['add'][$column]=TRUE;
            }
        }
    }

    /*public function addValue($column, $value) {
        if (!count($this->rows[$this->rowId])) {
            $this->rows[$this->rowId] = array_fill_keys(array_keys($this->columns), NULL);
        }
        $column = str_replace(".", "_", $column);
        $this->addColumn($column);
        $this->addToRow($column, $value);
    }*/

    /*private function addToRow($column, $value) {
        $this->columns[$column]->checkType($value);
        $this->rows[$this->rowId][$column] = $value;
    }

    public function addRow() {
        if (!isset($this->rows[$this->rowId]) || count($this->rows[$this->rowId])) {
            $this->rowId++;
            $this->rows[$this->rowId] = array();
        }
    }

    public function getNumOfRows() {
        return $this->rowId;
    }*/

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

    public function setDependants($array) {
        $this->dependecies = $array;
        if (count($this->dependecies)) {
            foreach ($this->dependecies as $col => $item) {
                $this->indexCache[$col] = array();
            }
        }
    }

    public function hasDependants() {
        return count($this->dependecies);
    }

    /*public function hasBeenImported($value, $key) {
        return \in_array($value, $this->indexCache[$key]);
    }

    public function removeRow($key) {
        if (count($this->dependecies) && count($this->rows[$key])) {
            foreach ($this->dependecies as $col => $item) {
                if(!\in_array($this->rows[$key][$col], $this->indexCache[$col]))
                $this->indexCache[$col][] = $this->rows[$key][$col];
            }
        }
        unset ($this->rows[$key]);
        $this->rowId--;
    }*/

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
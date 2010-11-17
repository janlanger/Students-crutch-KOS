<?php

namespace SAX\Entity;

/**
 * Description of Entity
 *
 * @author Jan Langer, kontakt@janlanger.cz
 */
class Entity {

    private $name;
    private $columns = array();
    private $primaryKeys = array();
    private $indexes = array();
    private $foreigns = array();
    public $dependecies;
    public $rows = array();
    private $rowId = -1;
    public $tableCreated = FALSE;
    private $indexCache = array();
    private $completeParse = FALSE;
    public $alterTable = array();

    public function __construct() {
        $this->rows[0] = array();
        $this->rowId= 0;
    }

    private function addColumn($column) {
        if (!isset($this->columns[$column])) {
            $this->columns[$column] = new Column($column, $this);
            if ($this->rowId >= 0) {
                foreach ($this->rows as $key => $row) {
                    if (!array_key_exists($column, $row)) {
                        $this->rows[$key][$column] = NULL;
                    }
                }
            }
        }
    }

    public function addValue($column, $value) {
        if (!count($this->rows[$this->rowId])) {
            $this->rows[$this->rowId] = array_fill_keys(array_keys($this->columns), NULL);
        }
        $column = str_replace(".", "_", $column);
        $this->addColumn($column);
        $this->addToRow($column, $value);
    }

    private function addToRow($column, $value) {
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
    }

    public function getName() {
        return $this->name;
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
        $this->primaryKeys[] = $k;
    }

    public function addIndex($k) {
        $this->indexes[] = $k;
    }

    public function addForeignKey($k, $c) {
        $this->foreigns[$k] = array("type" => $k, "foreign" => $c);
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

    public function hasBeenImported($value, $key) {
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
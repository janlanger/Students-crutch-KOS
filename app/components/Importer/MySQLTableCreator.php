<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MySQLTableCreator
 *
 * @author Honza
 */
class MySQLTableCreator extends NObject {



    private $name;
    private $columns;
    private $primaryKeys;
    private $indexes;
    private $foreignKeys;

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setColumns($columns) {
        $this->columns = $columns;
        return $this;
    }

    public function setPrimaryKeys($primaryKeys) {
        $this->primaryKeys = $primaryKeys;
        return $this;
    }

    public function setIndexes($indexes) {
        $this->indexes = $indexes;
        return $this;
    }
    public function setForeignKeys($foreignKeys) {
        $this->foreignKeys = $foreignKeys;
        return $this;
    }
    
    public function createReferences() {
        if($this->name=="" || !count($this->foreignKeys)) {
            return;
        }
        
        $ref=array();
        foreach($this->foreignKeys as $key) {
            $column=explode(".", $key['reference']);
            dibi::query('ALTER TABLE ['.$this->name.'] ADD FOREIGN KEY (['.$key['column'].']) REFERENCES ['.$column[0].'] (['.$column[1].'])');
        }
        
        
    }

    
    public function create() {
        if($this->name=="" || !count($this->columns)) {
            throw new InvalidStateException();
        }
        if (empty($this->columns)) {
            return;
        }
        $sql = "CREATE TABLE [" . strtolower($this->name) . "] (\n";
        $data = array();
        foreach ($this->columns as $column) {
            $data[] = "[" . $column->name . "] " . $column->getType() . " NULL";
        }
        $sql.=implode(", \n", $data);

        if (count($this->primaryKeys))
            $sql.=', PRIMARY KEY (' . implode(",", $this->primaryKeys) . ")";

        if (count($this->indexes )) {
            foreach($this->indexes as $key=>$index) {
                $this->indexes[$key]="INDEX([".$index."])";
            }
            $sql.=", ".implode(", ", $this->indexes);
        }


        $sql.=") ENGINE=INNODB";
        
        dibi::query($sql); //vytvoreni tabulky
        
    }



}
?>

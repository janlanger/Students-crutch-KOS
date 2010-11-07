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
class MySQLDatabaseManager extends NObject implements IDatabaseManager {
    /* private $name;
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




      } */

    private function createTable(XMLi_Entity $table) {
        if (empty($table->columns)) {
            return;
        }

        $columns = array();
        foreach ($table->columns as $column) {
            $columns[] = "[" . $column->name . "] " . $column->type . " NULL";
        }
        $columns = implode(", \n", $columns);

        $sql = 'CREATE TABLE [' . $table->name . '] (';
        $sql.=$columns;

        if (count($table->primaryKeys)) {
            $sql.=", PRIMARY KEY(" . implode(", ", $table->primaryKeys) . ')';
        }

        $indexes = array();
        if (count($table->indexes)) {
            foreach ($table->indexes as $key => $index) {
                $indexes[$key] = "INDEX([" . $index . "])";
            }
            $sql.=", " . implode(", ", $indexes);
        }
        $sql.=") ENGINE=InnoDB";

        dibi::query($sql);

        $this->createConstrains($table);
    }

    private function createConstrains(XMLi_Entity $table) {
        if (empty($table->foreignKeys)) {
            return;
        }

        $ref = array();
        $sql = 'ALTER TABLE [' . $table->name . ']';

        foreach ($table->foreignKeys as $key) {
            $column = explode(".", $key['reference']);
            $ref[] = ' ADD FOREIGN KEY ([' . $key['column'] . ']) REFERENCES [' . $column[0] . '] ([' . $column[1] . '])';
        }
        $sql.=implode(", \n", $ref);
        dibi::query($sql);
    }

    private function fillTable(XMLi_Entity $table) {
        $rows = $table->getRows();
        //array_walk($rows, array($this, 'makeHash'));

        $maxRowsPerInsert = 1000;
        $rows = array_chunk($rows, $maxRowsPerInsert);

        for ($i = 0; $i < count($rows); $i++) {
            //dibi::insert($table->name, $rows[$i])->execute();
            dibi::query("INSERT INTO [" . $table->name . "] %ex", $rows[$i]); //data
        }
    }

    public function createDatabase($name) {
        dibi::query("CREATE DATABASE [" . $name . "] COLLATE 'utf8_czech_ci'");
    }

    public function dropDatabase($name) {
        dibi::query("DROP DATABASE IF EXISTS [" . $name . "]");
    }

    public function fillDatabase($tables) {
        //TODO razeni zavislosti
        foreach ($tables as $table) {
            $this->createTable($table);
        }

        dibi::query("SET foreign_key_checks = 0");
        foreach ($tables as $table) {
            $this->fillTable($table);
        }
        dibi::query("SET foreign_key_checks = 1");
    }

    public function setDefaultDatabase($name) {
        dibi::query("USE [$name]");
    }

}

?>

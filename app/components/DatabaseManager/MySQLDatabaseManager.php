<?php

use SAX\Entity\Entity;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MySQLTableCreator
 *
 * @author Honza
 */
class MySQLDatabaseManager extends \Nette\Object implements IDatabaseManager {

    private static $typeMap = array(
        "string" => "varchar(255)",
        "int" => "bigint",
        "text" => "text"
    );

    public function createTable(Entity $table) {
        if (!count($table->getColumns())) {
            return;
        }

        $columns = array();
        foreach ($table->getColumns() as $column) {
            $columns[] = "[" . $column->name . "] " . $this->getNativeType($column->getType()) . " NULL";
        }
        $columns = implode(", \n", $columns);

        $sql = 'CREATE TABLE [' . $table->getName() . '] (';
        $sql.=$columns;

        if (count($table->getPrimaryKeys())) {
            $sql.=", PRIMARY KEY(" . implode(", ", $table->getPrimaryKeys()) . ')';
        }

        $indexes = array();
        if (count($table->getIndexes())) {
            foreach ($table->getIndexes() as $key => $index) {
                $indexes[$key] = "INDEX([" . $index . "])";
            }
            $sql.=", " . implode(", ", $indexes);
        }
        $sql.=") ENGINE=InnoDB";

        dibi::query($sql);
        $table->tableCreated = TRUE;

        $this->createConstrains($table);
    }

    private function getNativeType($type) {
        return (isset(self::$typeMap[$type]) ? self::$typeMap[$type] : "varchar(255)");
    }

    private function createConstrains(Entity $table) {
        if (!count($table->getForeigns())) {
            return;
        }

        $ref = array();
        $sql = 'ALTER TABLE [' . $table->getName() . ']';
        
        foreach ($table->getForeigns() as $key) {
            //$key['foreign'] = explode(".", $key['foreign']);
            $ref[] = ' ADD FOREIGN KEY ([' . $key['type'] . ']) REFERENCES [' . $key['foreign'][0] . '] ([' . $key['foreign'][1] . '])';
        }
        $sql.=implode(", \n", $ref);
        dibi::query($sql);
    }

    public function fillTables($data) {
        
        foreach ($data as $table => $rows) {
            $maxRowsPerInsert = 500;
            $rows = array_chunk($rows, $maxRowsPerInsert);

            for ($i = 0; $i < count($rows); $i++) {
                $sql=array();
                foreach($rows[$i] as $row) {
                    $data="(";
                    array_walk($row, function (&$item, $key) {
                        if(!$item) $item="NULL";
                        else $item="'".mysql_real_escape_string($item)."'";
                    });
                    $data .= implode(", ",$row);
                    $sql[]=$data.")";
                }
                $keys=array_keys($row);
                array_walk($keys, function (&$item, $key) {
                        $item="`".mysql_real_escape_string($item)."`";
                    });
                dibi::getConnection()->nativeQuery("INSERT INTO `" . $table . "` (".implode(", ",$keys).") VALUES ".implode(", ",$sql)); //data
            }
        }
    }

    public function alterTable(Entity $entity) {
        $sql='ALTER TABLE ['.$entity->getName().']';
        $alters=array();
        foreach ($entity->alterTable as $value) {
            
            $alters[]='MODIFY ['.$value.'] '.$this->getNativeType($entity->getColumn($value)->getType()).' NULL';
        }
        $sql.=implode(", ",$alters);
        dibi::query($sql);
        $entity->alterTable=array();
    }

    public function createDatabase($name) {
        try {
            dibi::query("CREATE DATABASE [" . $name . "] COLLATE 'utf8_czech_ci'");
        } catch (DibiException $e) {
            throw new DatabaseManagerException('Unable to create database. Already exists?', 0, $e);
        }
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

    public function getTables($database) {
        $result = dibi::query('SHOW TABLES IN [' . $database . ']');
        $tables = array();
        foreach ($result as $table) {
            $tables[] = $table['Tables_in_' . $database];
        }
        return $tables;
    }

    public function createRevision($fromDb, $toDb, $tables) {
        try {
            $this->createDatabase($toDb);
            foreach ($tables as $table) {
                $this->copyTable($table, $fromDb, $toDb);
            }
        } catch (DatabaseManagerException $e) {
            //rollback
            $this->dropDatabase($toDb);
            throw $e;
        }
    }

    public function copyTable($table, $fromDb, $toDb) {
        try {
            dibi::query("CREATE TABLE [$toDb.$table] SELECT * FROM [$fromDb.$table]");
        } catch (DibiException $e) {
            throw new DatabaseManagerException("Unable to copy table. " . $e->getMessage(), NULL, $e);
        }
    }

    public function getDatabaseSize($database) {
        try {
            return dibi::query("SELECT SUM([data_length] + [index_length]) AS [size] FROM [information_schema.tables] WHERE [table_schema]=%s", $database)->fetchSingle();
        } catch (DibiException $e) {
            throw new DatabaseManagerException($e->getMessage(), NULL, $e);
        }
    }

}

?>

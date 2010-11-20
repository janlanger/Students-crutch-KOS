<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ITableCreator
 *
 * @author Honza
 */
interface IDatabaseManager {
    public function dropDatabase($name);
    public function setDefaultDatabase($name);
    public function createDatabase($name);
    public function fillDatabase($tables);
    public function copyTable($table,$fromDb,$toDb);
    public function getDatabaseSize($database);
    public function createRevision($fromDb, $toDb, $tables);
}
?>

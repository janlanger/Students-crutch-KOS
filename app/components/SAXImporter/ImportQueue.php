<?php

namespace SAX\Queue;
use SAX\Entity\Entity;
use SAX\Entity\EntityDefinition;

/**
 * Description of ImportQueue
 *
 * @author Jan Langer, kontakt@janlanger.cz
 */
class ImportQueue {

    private $dataQueue = array();
    private $tableQueue = array();
    private $keyCache = array();
    /** @var IndexCache */
    private $indexCache = NULL;
    private $calls=0;
    /**
     *
     * @var \MySQLDatabaseManager
     */
    private $databaseCreator;

    public function __construct() {
        $this->loadIndexDefinition();
        $this->databaseCreator = \Nette\Environment::getService('IDatabaseManager');
        $this->indexCache = new IndexCache($this->keyCache);
    }

    public function add(Entity $entity) {

        if ($entity->hasData()) {
            $this->dataQueue[] = $entity;
            if(!isset($this->tableQueue[$entity->getDefinition()->getName()])) {
                $this->tableQueue[$entity->getDefinition()->getName()]=$entity->getDefinition();
            }
        }
        $this->flush();
    }

    public function flush($all=FALSE) {
        $this->calls++;
        foreach($this->tableQueue as $key=>$table) {
                $this->createTable($table);
                if($table->tableCreated && $table->isParseCompleted()) {
                    //unset($this->tableQueue[$key]);
            }
        }
        if($this->calls%200 == 0 || $all) {
            $this->flushEntityCache();
        }
    }

    private function flushEntityCache() {
        foreach ($this->dataQueue as $key=>$entity) {
            if($entity->getDefinition()->tableCreated && $this->checkDependency($entity)) {
                $this->databaseCreator->fillTable($entity);
                $this->indexCache->add($entity);
                $this->dataQueue[$key]->free();
                unset($this->dataQueue[$key]);
            }
        }
    }

    private function createTable(EntityDefinition $table) {
        if(!$table->tableCreated) {
            $this->setIndexes($table);
            //$this->initIndexCacheFor($table);
            $this->databaseCreator->createTable($table);
        } elseif (count($table->alterTable)) {
            $this->databaseCreator->alterTable($table);
        }
    }

    private function checkDependency(Entity $entity) {
        return $this->indexCache->hasFulfilledDeps($entity);
    }

    private function setIndexes(EntityDefinition $entity) {
        if (!count($this->keyCache)) {
            $this->loadIndexDefinition();
        }
        if (isset($this->keyCache[$entity->getName()])) {
            $in = $this->keyCache[$entity->getName()];
            foreach ($in as $key => $index) {
                if ($index['type'] == 'primary') {
                    $entity->addPrimaryKey($key);
                }
                if ($index['type'] == 'index') {
                    $entity->addIndex($key);
                }
                if ($index['type'] == 'foreign') {
                    
                    if(!isset($this->tableQueue[$index['foreign'][0]]) || !$this->tableQueue[$index['foreign'][0]]->hasColumn($index['foreign'][1])) {
                        $this->indexCache->removeDependency($entity->getName(), $key, $index['foreign'][0], $index['foreign'][1]);
                        
                    }
                    $entity->addForeignKey($key, $index['foreign']);
                }
            }
        }
    }






    private function loadIndexDefinition() {
        $result = \IndexDefinition::find();
        foreach ($result as $row) {
            $this->keyCache[$row->table][$row->column] = array("type" => $row->index_type, "foreign" => explode(".",$row->foreign));
        }
    }


    public function getQueueSize() {
        return count($this->dataQueue);
    }

}

?>

<?php

namespace SAX;

/**
 * Description of ImportQueue
 *
 * @author Jan Langer, kontakt@janlanger.cz
 */
class ImportQueue {

    private $queue = array();
    private $indexCache = array();
    private $dependencyCache=array();
    /**
     *
     * @var \MySQLDatabaseManager
     */
    private $databaseCreator;

    public function __construct() {
        $this->databaseCreator = \Nette\Environment::getService('IDatabaseManager');
    }

    public function add(Entity\Entity $entity) {
        if (!isset($this->queue[$entity->getName()])) {
            $this->queue[$entity->getName()] = $entity;
        }
        $this->flushQueue();
    }

    public function flushQueue() {
        foreach ($this->queue as $key=>$entity) {
            $this->flushEntity($entity);
            if(!\count($entity->rows) && $entity->isParseCompleted() && !$entity->hasDependants()) {
                unset($this->queue[$key]);
            }
            
        }
    }

    private function flushEntity(Entity\Entity $entity) {
        //TODO DEPENDENCIES
        if (!$entity->tableCreated) {
            $this->setIndexes($entity);
            $entity->setDependants($this->getDependantsFor($entity));
            $this->databaseCreator->createTable($entity);
        }
        elseif(count($entity->alterTable)) {
            $this->databaseCreator->alterTable($entity);
        }
        
        $toInsert = array();
        foreach ($entity->rows as $key => $row) {
            if ($this->checkDependency($entity,$row)) {
                if (!isset($toInsert[$entity->getName()])) {
                    $toInsert[$entity->getName()] = array();
                }
                if(count($row)) {
                    $toInsert[$entity->getName()][] = $row;
                }
                $entity->removeRow($key);
            }
        }
        if (count($toInsert)) {
            $this->databaseCreator->fillTables($toInsert);
        }
    }

    private function checkDependency(Entity\Entity $entity, $row) {
        $return = TRUE;
        foreach ($entity->getForeigns() as $key=>$value) {
            if(!isset ($row[$key])) {
                $return = TRUE;
                continue;
            }

            //$value['foreign']=\explode(".", $value['foreign']); //0 - table, 1 - col

            if(isset($this->queue[$value['foreign'][0]])) {
                if($this->queue[$value['foreign'][0]]->hasBeenImported($row[$key],$value['foreign'][1])) {
                    $return = TRUE;
                }
                else {
                    $return = FALSE;
                }
            }
            iF(!$return) {
                break;  //aspon jeden nevyhovuje - neimportovat
            }
        }
        return $return;
    }

    private function setIndexes(Entity\Entity $entity) {
        if (!count($this->indexCache)) {
            $this->loadIndexDefinition();
        }
        if (isset($this->indexCache[$entity->getName()])) {
            $in = $this->indexCache[$entity->getName()];
            foreach ($in as $key => $index) {
                if ($index['type'] == 'primary') {
                    $entity->addPrimaryKey($key);
                }
                if ($index['type'] == 'index') {
                    $entity->addIndex($key);
                }
                if ($index['type'] == 'foreign') {
                    $entity->addForeignKey($key, $index['foreign']);
                }
            }
        }
    }

    private function getDependantsFor(Entity\Entity $entity) {
        if (!count($this->dependencyCache)) {
            $this->buildDependencyCache();
        }
        if(isset($this->dependencyCache[$entity->getName()])) {
            return $this->dependencyCache[$entity->getName()];
        }
        return array();
    }

    private function buildDependencyCache() {
        if (!count($this->indexCache)) {
            $this->loadIndexDefinition();
        }

        foreach($this->indexCache as $table=>$columns) {
            foreach($columns as $col=>$index) {
                if($index['type']=='foreign') {
                    //$index['foreign']=\explode(".", $index['foreign']);
                    if(!isset($this->dependencyCache[$index['foreign'][0]])) {
                        $this->dependencyCache[$index['foreign'][0]]=array();
                    }
                    if(!isset($this->dependencyCache[$index['foreign'][0]][$index['foreign'][1]])) {
                        $this->dependencyCache[$index['foreign'][0]][$index['foreign'][1]]=array();
                    }
                    $this->dependencyCache[$index['foreign'][0]][$index['foreign'][1]][]=array($table,$col);
                }
            }
        }
    }

    private function loadIndexDefinition() {
        $result = \IndexDefinition::find();
        foreach ($result as $row) {
            $this->indexCache[$row->table][$row->column] = array("type" => $row->index_type, "foreign" => explode(".",$row->foreign));
        }
    }

}

?>

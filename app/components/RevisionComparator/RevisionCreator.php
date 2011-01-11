<?php
/**
 * Description of RevisionCreator
 *
 * @author Jan Langer, kontakt@janlanger.cz
 */
class RevisionManipulator extends \Nette\Application\Control {

    /** @var MySQLDatabaseManager $manager */
    private $manager=null;
    private $live_database;

    public function setManager($manager) {
        $this->manager = $manager;
    }

    public function setLive_database($live_database) {
        $this->live_database = $live_database;
    }


    public function create(Revision $rev) {
        try {
            $definition=$rev->getDefinition();
            $this->manager->createRevision($this->live_database, $rev->db_name, $definition);
            dibi::delete(":main:revision_to_create")->where(array("rev_id"=>$rev->rev_id))->execute();
        }
        catch (Exception $e) {
            $this->manager->dropDatabase($rev->db_name);
            throw new ModelException('Cannot create revision: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    public function update(Revision $revision) {
        
        $comparator=new RevisonComparator(NULL, NULL);
        $comparator->setFirst(@reset(Revision::find(array('app_id'=>$revision->app_id,'db_name'=>'rozvrh_live'))));
        $comparator->setSecond($revision);
        try{
            $definition=$revision->getDefinition();
            $structure=$comparator->compareStructure(TRUE);
            $dataCompare=$comparator->compareData();
            $tablesInfo=$this->manager->getTableInfo($this->live_database, $definition);

            foreach($definition->getTables() as $table) {
                
                if($definition->getSchema($table)=='data') {
                    
                    if(!isset($structure[$table])) { //0 diferencies - same tables
                        if(count($dataCompare[$table])!= 0) {
                        if(count($dataCompare[$table]) <= $definition->getMaxChanges($table) || $definition->getMaxChanges($table)<0) {
                            $this->synchronizeData($table, $revision);
                        }
                        else {
                            //TODO - informovani
                            $this->getPresenter()->getApplication()->getService('ILogger')->logMessage("Too many changes in table ".$table.", revision ".$revision->alias,  Logger::NOTICE, 'Update-CLI');
                        }
                        }
                    }
                    else {
                        $this->getPresenter()->getApplication()->getService('ILogger')->logMessage("Structure of ".$table." has changed, revision ".$revision->alias,  Logger::NOTICE, 'Update-CLI');
                    }
                }
                elseif($definition->getSchema($table)=='structure') {
                    try {
                        $this->manager->dropTable($revision->db_name.'.'.$table);
                        $this->manager->copyTable($table, $tablesInfo[$table], $this->live_database, $revision->db_name, $revision->getDefinition()->getCondition($table));
                    } catch (DatabaseManagerException $e) {
                        $this->getPresenter()->getApplication()->getService('ILogger')->logMessage("Copy of ".$table." failed, revision ".$revision->alias.', '.$e->getMessage(),  Logger::NOTICE, 'Update-CLI');
                    }
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function synchronizeData($table, $revision) {
        $definition=$revision->getDefinition();

        $this->manager->updateTable($table,  
                array_keys($definition->getColumns($table)),
                $definition->getCondition($table) ,
                $this->live_database,
                $revision->db_name);

    }

}
?>

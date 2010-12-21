<?php
/**
 * Description of RevisionCreator
 *
 * @author Jan Langer, kontakt@janlanger.cz
 */
class RevisionCreator {

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

}
?>

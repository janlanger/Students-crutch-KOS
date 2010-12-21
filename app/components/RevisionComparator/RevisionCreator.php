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
        $definition=$rev->getDefinition();
        $this->manager->createRevision($this->live_database, $rev->db_name, $definition);
    }

}
?>

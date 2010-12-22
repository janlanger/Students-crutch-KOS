<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Functions
 *
 * @author Honza
 */
class Revision extends Model {

    private $definition;

    public static function find($where = NULL, $order = NULL, $offset = NULL, $limit = NULL) {
        $q = dibi::select("*")->from("[:main:revision]");
        if ($where != NULL)
            $q->where($where);
        if ($order != NULL)
            $q->orderBy($order);
        if ($limit != NULL)
            $q->limit($limit);
        if ($offset != NULL)
            $q->offset($offset);

        return $q->execute()->setRowClass(get_called_class())->fetchAssoc('rev_id');
    }


    public static function getToCreate() {
        $q=dibi::select("rev_id, id")->from(":main:revision_to_create")->fetchAll();
        $data=array();
        foreach($q as $row) {
            $data[$row['rev_id']]=@reset(self::find(array("rev_id"=>$row['rev_id'])));
        }
        return $data;
    }

    public static function getToUpdate() {
        $q=dibi::select("rev_id")->from(":main:revision_table_definition")->setFlag('DISTINCT')->fetchAll();
        $data=array();
        foreach($q as $row) {
            $data[$row['rev_id']]=@reset(self::find(array("rev_id"=>$row['rev_id'])));
        }
        return $data;
    }

    public static function getAvaiableTables($database=NULL) {
        if ($database == NULL)
            $database = \Nette\Environment::getConfig('xml')->liveDatabase;
        $_this = new self(array("db_name"=>$database));
        $databaseManager = \Nette\Environment::getContext()->getService('IDatabaseManager');
        $databaseManager->setDefaultDatabase($database);
        return $_this->getTables();
    }

    public static function create($name, $app_id, $isMain, $database, $tables, $from=NULL) {
        if (!count($tables)) {
            throw new ModelException('Revize musí obsahovat alespoň jednu tabulku.');
        }

        if (dibi::select('count(*)')->from(":main:revision")->where(array("app_id" => $app_id, 'alias' => $name))->execute()->fetchSingle()) {
            throw new ModelException('Revizi s tímto názvem nelze vytvořit. Pravděpodobně již existuje.');
        }

        if ($isMain) {
            if (dibi::select('count(*)')->from(":main:revision")->where(array("app_id" => $app_id, 'isMain' => $isMain))->execute()->fetchSingle()) {
                throw new ModelException('Nelze vytvořit více revizí označených jako výchozí.');
            }
        }

        if ($from == NULL)
            $from = \Nette\Environment::getConfig('xml')->liveDatabase;

       // $databaseManager = \Nette\Environment::getContext()->getService('IDatabaseManager');
        try {
            //$databaseManager->createRevision($from, $database, $tables);
            dibi::begin();
            dibi::insert(":main:revision", array(
                "app_id" => $app_id,
                "alias" => $name,
                "db_name" => $database,
                "isMain" => $isMain
            ))->execute();
            $rev_id=dibi::getInsertId();
            foreach($tables as $table=>$items) {
                dibi::insert(":main:revision_table_definition", array(
                    'rev_id'=>$rev_id,
                    'table'=>$table,
                    'columns'=>  serialize($items['columns']),
                    'condition' => $items['condition'],
                    "schema" => $items['schema'],
                    'max_changes' => (isset($items['max_changes'])?$items['max_changes']:0)
                ))->execute();
            }
            dibi::insert(":main:revision_to_create", array("rev_id"=>$rev_id))->execute();
            dibi::commit();
            return TRUE;
        } catch (DibiException $e) {
            dibi::rollback();
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getTables() {
        $databaseManager = \Nette\Environment::getContext()->getService('IDatabaseManager');
        try {
            return $databaseManager->getDatabaseStructure($this->db_name);


        } catch (DatabaseManagerException $e) {
            throw new ModelException('Nepodařilo se získat seznam tabulek. ' . $e->getMessage(), NULL, $e);
        }
    }

    public function getDatabaseSize() {
        return $databaseManager = \Nette\Environment::getContext()->getService('IDatabaseManager')->getDatabaseSize($this->db_name);
    }

    public function delete() {
        $databaseManager = \Nette\Environment::getContext()->getService('IDatabaseManager');
        try {
            $databaseManager->dropDatabase($this->db_name);
            dibi::delete(':main:revision')->where(array("rev_id" => $this->rev_id))->execute();
        } catch (DatabaseManagerException $e) {
            throw new ModelException('Nepodařilo se smazat databázi ' . $name . '. ' . $e->getMessage(), NULL, $e);
        } catch (DibiException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function setValues($values) {
        if (dibi::select('count(*)')->from(":main:revision")
                        ->where("app_id=%s", $this->app_id)
                        ->and('alias=%s', $values['name'])
                        ->and('[alias]!=%s', $this->alias)
                        ->execute()->fetchSingle()) {
            throw new ModelException('Revizi s tímto názvem nelze vytvořit. Pravděpodobně již existuje.');
        }

        if ($values['isMain']) {
            if (dibi::select('count(*)')->from(":main:revision")
                            ->where("app_id=%s", $this->app_id)
                            ->and('isMain=%b', $values['isMain'])
                            ->and("[alias]!=%s", $this->alias)
                            ->execute()->fetchSingle()) {
                throw new ModelException('Nelze vytvořit více revizí označených jako výchozí.');
            }
        }
        $this->alias = $values['name'];
        $this->isMain = $values['isMain'];
        return $this;
    }

    public function save() {
        try {
            dibi::update(":main:revision", array(
                "alias"=>$this->alias,
                "isMain"=>$this->isMain
            ))->where("rev_id=%i",$this->rev_id)->execute();
        } catch (DibiException $e) {
            throw new ModelException('Nepodařilo se uložit data.', NULL, $e);
        }
    }

    public function getDefinition() {
        if($this->definition==NULL) {
            $this->definition=RevisionDefinition::find(array("rev_id"=>$this->rev_id));
        }
        return ($this->definition);
    }

}

class RevisionDefinition {

    private $columns;
    private $tables;
    private $conditions;
    private $schemas;
    private $max_changes;

    public static function find($where = NULL, $order = NULL, $offset = NULL, $limit = NULL) {
        $q = dibi::select("*")->from("[:main:revision_table_definition]");
        if ($where != NULL)
            $q->where($where);
        if ($order != NULL)
            $q->orderBy($order);
        if ($limit != NULL)
            $q->limit($limit);
        if ($offset != NULL)
            $q->offset($offset);

        $result=$q->fetchAssoc('id');
        $_this=new self();
        foreach($result as $res) {
            $_this->tables[]=$res->table;
            $_this->columns[$res->table]=unserialize($res->columns);
            $_this->conditions[$res->table]=$res->condition;
            $_this->schemas[$res->table]=$res->schema;
            if($res->schema=='data') {
                $_this->max_changes[$res->table]=$res->max_changes;
            }

        }
        return $_this;
    }


    public function hasCondition($table) {
        return isset($this->conditions[$table]) && $this->conditions[$table]!="";
    }
    public function getCondition($table) {
        return $this->conditions[$table];
    }

    public function getColumns($table=NULL) {
        if($table==NULL) {
            return $this->columns;
        }
        elseif(isset($this->columns[$table])) {
            return $this->columns[$table];
        }
        return array();
    }

    
    public function getTables() {
        return $this->tables;
    }


}

?>

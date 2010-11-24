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

        $databaseManager = \Nette\Environment::getContext()->getService('IDatabaseManager');
        try {
            $databaseManager->createRevision($from, $database, $tables);
            dibi::insert(":main:revision", array(
                "app_id" => $app_id,
                "alias" => $name,
                "db_name" => $database,
                "isMain" => $isMain
            ))->execute();
            return TRUE;
        } catch (DatabaseManagerException $e) {
            throw new ModelException('Unable to create revision ' . $name . '. ' . $e->getMessage(), NULL, $e);
        } catch (DibiException $e) {
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

}

?>
